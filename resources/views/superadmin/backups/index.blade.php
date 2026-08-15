@extends('layouts.superadmin')

@section('title', 'Tenant Backup & Recovery Control Center')

@section('content')
<style>
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-ring: rgba(37, 99, 235, 0.18);
        --success: #16a34a;
        --warning: #d97706;
        --danger: #dc2626;
        --bg-surface: #ffffff;
        --border-color: #e2e8f0;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --text-subtle: #94a3b8;
        --radius-lg: 16px;
        --radius-md: 10px;
        --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        --transition-fast: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .backups-container {
        padding: 24px;
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Page Header */
    .header-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        background: var(--bg-surface);
        padding: 20px 24px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }
    .header-left h1 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main);
        margin: 0 0 4px 0;
        letter-spacing: -0.5px;
    }
    .header-left p {
        font-size: 13px;
        color: var(--text-muted);
        margin: 0 0 8px 0;
    }
    .header-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
    .header-status-badge .pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
    }
    .header-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .btn-action-primary {
        background: var(--primary);
        color: #ffffff;
        border: none;
        padding: 9px 16px;
        border-radius: var(--radius-md);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all var(--transition-fast);
    }
    .btn-action-primary:hover {
        background: var(--primary-hover);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }
    .btn-action-secondary {
        background: #ffffff;
        color: var(--text-main);
        border: 1px solid var(--border-color);
        padding: 9px 16px;
        border-radius: var(--radius-md);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all var(--transition-fast);
    }
    .btn-action-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .kpi-card {
        background: var(--bg-surface);
        padding: 18px 20px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        transition: transform var(--transition-fast), box-shadow var(--transition-fast);
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .kpi-card .card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .kpi-card .card-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-card .icon-pill {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }
    .kpi-card .metric-val {
        font-size: 26px;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.1;
    }
    .kpi-card .metric-foot {
        font-size: 11.5px;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: 600;
    }
    .metric-foot.positive { color: var(--success); }
    .metric-foot.warning { color: var(--warning); }
    .metric-foot.danger { color: var(--danger); }
    .metric-foot.muted { color: var(--text-subtle); }

    /* Health Overview Box */
    .health-overview-panel {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        padding: 20px 24px;
        margin-bottom: 24px;
    }
    .health-overview-title {
        font-size: 13px;
        font-weight: 800;
        color: var(--text-main);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .health-progress-bar {
        height: 12px;
        border-radius: 20px;
        background: #e2e8f0;
        overflow: hidden;
        display: flex;
        margin-bottom: 16px;
    }
    .health-progress-seg {
        height: 100%;
        transition: width 0.4s ease;
    }
    .health-legend-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 16px;
    }
    .health-legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .health-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* Separated Table Grid Styling */
    .grid-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        min-width: 1100px;
        border: 1px solid var(--border-color);
    }
    .grid-table th {
        padding: 12px 14px;
        background: #f8fafc;
        border-bottom: 2px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        font-size: 11px;
        font-weight: 800;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .grid-table th:last-child {
        border-right: none;
    }
    .grid-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
    }
    .grid-table td:last-child {
        border-right: none;
    }

    /* Filter Toolbar */
    .backups-toolbar {
        background: var(--bg-surface);
        padding: 16px 20px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        flex: 1;
    }
    .search-box {
        position: relative;
        min-width: 260px;
        flex: 1;
        max-width: 360px;
    }
    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-subtle);
        font-size: 14px;
    }
    .search-box input {
        width: 100%;
        padding: 9px 12px 9px 36px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        font-size: 13px;
        outline: none;
        background: #ffffff;
        transition: all var(--transition-fast);
    }
    .search-box input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-ring);
    }
    .filter-select {
        padding: 9px 14px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: #ffffff;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-main);
        outline: none;
        cursor: pointer;
    }
    .filter-select:focus {
        border-color: var(--primary);
    }

    /* Floating Contextual Bulk Action Bar */
    .bulk-action-bar {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #0f172a;
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 40px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        gap: 16px;
        z-index: 250;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .bulk-action-bar.visible {
        transform: translateX(-50%) translateY(0);
    }

    /* Slide-Over Drawer */
    .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        z-index: 400;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .drawer-overlay.open {
        opacity: 1;
        visibility: visible;
    }
    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        width: 100%;
        max-width: 620px;
        height: 100vh;
        background: #ffffff;
        box-shadow: -10px 0 40px rgba(0, 0, 0, 0.15);
        z-index: 401;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    .drawer-panel.open {
        transform: translateX(0);
    }
    .drawer-header {
        padding: 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        background: #f8fafc;
    }
    .drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .drawer-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Modal Overlay & Box */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
    }
    .modal-overlay.open {
        opacity: 1;
        visibility: visible;
    }
    .modal-box {
        background: #ffffff;
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 580px;
        padding: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid var(--border-color);
    }

    /* Log Terminal Box */
    .log-terminal {
        background: #090d16;
        color: #38bdf8;
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        font-size: 12px;
        line-height: 1.6;
        padding: 16px;
        border-radius: 10px;
        max-height: 320px;
        overflow-y: auto;
        border: 1px solid #1e293b;
    }

    /* Badge Pills */
    .badge-status-healthy { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .badge-status-duesoon { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .badge-status-failed  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .badge-status-never   { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

    .status-pill-lg {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
    }
</style>

<div class="backups-container">
    <!-- 1. PREMIER PAGE HEADER -->
    <div class="header-panel">
        <div class="header-left">
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1>BACKUPS</h1>
                <span class="header-status-badge">
                    <span class="pulse-dot"></span> Backup System Operational
                </span>
            </div>
            <p>Protect, monitor, and recover tenant databases across the platform.</p>
        </div>
        <div class="header-right">
            <button class="btn-action-secondary" id="refreshStatusBtn">
                <i class="fas fa-rotate"></i> Refresh Status
            </button>
            <a href="#backupHistorySection" class="btn-action-secondary">
                <i class="fas fa-history"></i> Backup History
            </a>
            <button class="btn-action-primary" id="openCreateBackupBtn">
                <i class="fas fa-plus-circle"></i> + Create Backup
            </button>
        </div>
    </div>

    <!-- 2. KPI CARDS GRID -->
    <div class="kpi-grid">
        <!-- TOTAL TENANTS -->
        <div class="kpi-card">
            <div class="card-head">
                <span class="card-label">Total Tenants</span>
                <div class="icon-pill" style="background: #eff6ff; color: #2563eb;"><i class="fas fa-building"></i></div>
            </div>
            <div class="metric-val">{{ number_format($kpi['total_tenants']) }}</div>
            <div class="metric-foot positive">
                <i class="fas fa-database"></i> Registered environments
            </div>
        </div>

        <!-- HEALTHY BACKUPS -->
        <div class="kpi-card">
            <div class="card-head">
                <span class="card-label">Healthy Backups</span>
                <div class="icon-pill" style="background: #f0fdf4; color: #16a34a;"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="metric-val">{{ number_format($kpi['healthy_backups']) }}</div>
            <div class="metric-foot positive">
                ● {{ $kpi['total_tenants'] > 0 ? round(($kpi['healthy_backups'] / $kpi['total_tenants']) * 100) : 100 }}% protected
            </div>
        </div>

        <!-- BACKUPS DUE -->
        <div class="kpi-card">
            <div class="card-head">
                <span class="card-label">Backups Due</span>
                <div class="icon-pill" style="background: #fffbeb; color: #d97706;"><i class="fas fa-clock-rotate-left"></i></div>
            </div>
            <div class="metric-val">{{ number_format($kpi['backups_due']) }}</div>
            <div class="metric-foot {{ $kpi['backups_due'] > 0 ? 'warning' : 'muted' }}">
                {{ $kpi['backups_due'] > 0 ? 'Requires backup run' : '0 overdue' }}
            </div>
        </div>

        <!-- FAILED BACKUPS -->
        <div class="kpi-card">
            <div class="card-head">
                <span class="card-label">Failed Backups</span>
                <div class="icon-pill" style="background: #fef2f2; color: #dc2626;"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
            <div class="metric-val">{{ number_format($kpi['failed_backups']) }}</div>
            <div class="metric-foot {{ $kpi['failed_backups'] > 0 ? 'danger' : 'muted' }}">
                {{ $kpi['failed_backups'] > 0 ? 'Needs immediate attention' : '0 error archives' }}
            </div>
        </div>

        <!-- BACKUP STORAGE -->
        <div class="kpi-card">
            <div class="card-head">
                <span class="card-label">Backup Storage</span>
                <div class="icon-pill" style="background: #f5f3ff; color: #7c3aed;"><i class="fas fa-hard-drive"></i></div>
            </div>
            <div class="metric-val" style="font-size: 22px;">{{ $totalStorageFormatted }}</div>
            <div class="metric-foot muted">
                Total archive space used
            </div>
        </div>

        <!-- LAST SUCCESSFUL BACKUP -->
        <div class="kpi-card">
            <div class="card-head">
                <span class="card-label">Last Backup Run</span>
                <div class="icon-pill" style="background: #ecfeff; color: #0891b2;"><i class="fas fa-calendar-check"></i></div>
            </div>
            <div class="metric-val" style="font-size: 19px; margin-top: 4px;">{{ $kpi['last_run'] }}</div>
            <div class="metric-foot positive">
                ● Auto-verify active
            </div>
        </div>
    </div>

    <!-- 3. BACKUP HEALTH OVERVIEW DISTRIBUTION BAR -->
    @php
        $total = max(1, $kpi['total_tenants']);
        $healthyPct = round(($kpi['healthy_backups'] / $total) * 100);
        $duePct     = round(($kpi['backups_due'] / $total) * 100);
        $failedPct  = round(($kpi['failed_backups'] / $total) * 100);
        $neverPct   = round(($kpi['never_backed_up'] / $total) * 100);
    @endphp
    <div class="health-overview-panel">
        <div class="health-overview-title">
            <span><i class="fas fa-chart-pie" style="color: var(--primary); margin-right: 6px;"></i> BACKUP HEALTH DISTRIBUTION</span>
            <span style="font-size: 11.5px; color: var(--text-subtle); text-transform: none; font-weight: 600;">Real-time tenant snapshot</span>
        </div>
        
        <div class="health-progress-bar">
            <div class="health-progress-seg" style="width: {{ $healthyPct }}%; background: #16a34a;" title="Healthy: {{ $kpi['healthy_backups'] }}"></div>
            <div class="health-progress-seg" style="width: {{ $duePct }}%; background: #d97706;" title="Due Soon: {{ $kpi['backups_due'] }}"></div>
            <div class="health-progress-seg" style="width: {{ $failedPct }}%; background: #dc2626;" title="Failed: {{ $kpi['failed_backups'] }}"></div>
            <div class="health-progress-seg" style="width: {{ $neverPct }}%; background: #94a3b8;" title="Never: {{ $kpi['never_backed_up'] }}"></div>
        </div>

        <div class="health-legend-grid">
            <div class="health-legend-item">
                <div class="health-legend-dot" style="background: #16a34a;"></div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: var(--text-main);">Healthy: {{ $kpi['healthy_backups'] }}</div>
                    <div style="font-size: 11px; color: var(--text-subtle);">Backed up in last 24h</div>
                </div>
            </div>

            <div class="health-legend-item">
                <div class="health-legend-dot" style="background: #d97706;"></div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: var(--text-main);">Due Soon: {{ $kpi['backups_due'] }}</div>
                    <div style="font-size: 11px; color: var(--text-subtle);">Pending scheduled backup</div>
                </div>
            </div>

            <div class="health-legend-item">
                <div class="health-legend-dot" style="background: #dc2626;"></div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: var(--text-main);">Failed: {{ $kpi['failed_backups'] }}</div>
                    <div style="font-size: 11px; color: var(--text-subtle);">Execution errors</div>
                </div>
            </div>

            <div class="health-legend-item">
                <div class="health-legend-dot" style="background: #94a3b8;"></div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: var(--text-main);">Never Backed Up: {{ $kpi['never_backed_up'] }}</div>
                    <div style="font-size: 11px; color: var(--text-subtle);">Newly provisioned</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. SEARCH & FILTER TOOLBAR -->
    <div class="backups-toolbar">
        <div class="toolbar-left">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="backupSearchInput" placeholder="Search company, tenant ID, database..." />
            </div>

            <!-- Status Filter -->
            <select class="filter-select" id="filterStatus">
                <option value="all">All Statuses ▼</option>
                <option value="healthy">🟢 Healthy</option>
                <option value="due_soon">🟡 Due Soon</option>
                <option value="failed">🔴 Failed</option>
                <option value="never">⚪ Never Backed Up</option>
            </select>

            <!-- Tenant Filter -->
            <select class="filter-select" id="filterTenant">
                <option value="all">All Tenants ▼</option>
                @foreach($tenantBackupData as $tData)
                    <option value="{{ $tData['company_id'] }}">{{ $tData['name'] }}</option>
                @endforeach
            </select>

            <!-- Backup Type Filter -->
            <select class="filter-select" id="filterType">
                <option value="all">Backup Type ▼</option>
                <option value="full">Full Backup</option>
                <option value="incremental">Incremental</option>
            </select>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <button class="btn-action-secondary" id="resetFiltersBtn">
                <i class="fas fa-filter-circle-xmark"></i> Reset
            </button>
            <button class="btn-action-secondary" id="exportBackupsBtn">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- 5. TENANT BACKUP STATUS DATA TABLE -->
    <div style="background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 24px;">
        <div style="overflow-x: auto;">
            <table class="grid-table" id="tenantBackupsTable">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 40px;">
                            <input type="checkbox" id="selectAllBackupsCheckbox" style="cursor: pointer;" />
                        </th>
                        <th style="text-align: left;">Company</th>
                        <th style="text-align: left;">Tenant ID</th>
                        <th style="text-align: left;">Database</th>
                        <th style="text-align: center;">Backup Status</th>
                        <th style="text-align: left;">Last Backup</th>
                        <th style="text-align: left;">Backup Size</th>
                        <th style="text-align: left;">Retention</th>
                        <th style="text-align: left;">Next Backup</th>
                        <th style="text-align: center;">Health</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenantBackupData as $row)
                    <tr class="backup-table-row" data-company-id="{{ $row['company_id'] }}" data-status="{{ $row['status'] }}" style="transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='transparent';">
                        <!-- Checkbox -->
                        <td style="text-align: center;">
                            <input type="checkbox" class="row-backup-checkbox" value="{{ $row['company_id'] }}" style="cursor: pointer;" />
                        </td>

                        <!-- Company -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 34px; height: 34px; border-radius: 8px; background: #f1f5f9; color: #334155; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12.5px; overflow: hidden; flex-shrink: 0; border: 1px solid var(--border-color);">
                                    @if(!empty($row['logo_url']))
                                        <img src="{{ $row['logo_url'] }}" alt="{{ $row['name'] }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                    @else
                                        {{ strtoupper(substr($row['name'], 0, 2)) }}
                                    @endif
                                </div>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13.5px;">{{ $row['name'] }}</strong>
                                    <div style="font-size: 11px; color: var(--text-subtle);">{{ $row['backups_count'] }} archives stored</div>
                                </div>
                            </div>
                        </td>

                        <!-- Tenant ID -->
                        <td>
                            <span style="font-family: monospace; font-size: 12px; font-weight: 700; background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 6px;">
                                {{ $row['code'] }}
                            </span>
                        </td>

                        <!-- Database -->
                        <td>
                            <span style="font-family: monospace; font-size: 12px; color: var(--text-main); font-weight: 600;">
                                {{ $row['db_name'] }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td style="text-align: center;">
                            @if($row['status'] === 'healthy')
                                <span class="status-pill-lg badge-status-healthy"><i class="fas fa-check-circle"></i> Healthy</span>
                            @elseif($row['status'] === 'due_soon')
                                <span class="status-pill-lg badge-status-duesoon"><i class="fas fa-clock"></i> Due Soon</span>
                            @elseif($row['status'] === 'failed')
                                <span class="status-pill-lg badge-status-failed"><i class="fas fa-times-circle"></i> Failed</span>
                            @else
                                <span class="status-pill-lg badge-status-never"><i class="fas fa-minus-circle"></i> Never</span>
                            @endif
                        </td>

                        <!-- Last Backup -->
                        <td>
                            <div style="font-weight: 600; color: var(--text-main);">{{ $row['last_backup'] }}</div>
                            <div style="font-size: 11px; color: var(--text-subtle);">{{ $row['last_backup_at'] }}</div>
                        </td>

                        <!-- Backup Size -->
                        <td style="font-family: monospace; font-weight: 700; color: var(--text-main);">
                            {{ $row['backup_size'] }}
                        </td>

                        <!-- Retention -->
                        <td style="color: var(--text-muted); font-size: 12.5px;">
                            {{ $row['retention'] }}
                        </td>

                        <!-- Next Backup -->
                        <td style="font-weight: 600; color: {{ $row['status'] === 'due_soon' ? '#d97706' : 'var(--text-main)' }}; font-size: 12.5px;">
                            {{ $row['next_backup'] }}
                        </td>

                        <!-- Health -->
                        <td style="text-align: center;">
                            @if($row['status'] === 'failed')
                                <span style="color: var(--danger); font-size: 12px; font-weight: 700;">● Error</span>
                            @else
                                <span style="color: var(--success); font-size: 12px; font-weight: 700;">● Verified</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td style="text-align: right;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                <button class="btn-action-secondary open-drawer-btn" 
                                        data-id="{{ $row['company_id'] }}"
                                        data-name="{{ $row['name'] }}"
                                        data-code="{{ $row['code'] }}"
                                        data-db="{{ $row['db_name'] }}"
                                        data-status="{{ $row['status'] }}"
                                        data-last="{{ $row['last_backup'] }}"
                                        data-size="{{ $row['backup_size'] }}"
                                        data-file="{{ $row['latest_file'] }}"
                                        data-logo="{{ $row['logo_url'] ?? '' }}"
                                        style="padding: 5px 10px; font-size: 12px;">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                
                                <button class="btn-action-primary trigger-single-backup-btn" 
                                        data-id="{{ $row['company_id'] }}"
                                        data-name="{{ $row['name'] }}"
                                        data-db="{{ $row['db_name'] }}"
                                        style="padding: 5px 10px; font-size: 12px;">
                                    <i class="fas fa-play"></i> {{ $row['status'] === 'failed' ? 'Retry' : 'Backup' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 40px; color: var(--text-subtle);">
                            <i class="fas fa-hard-drive" style="font-size: 32px; margin-bottom: 8px; display: block;"></i>
                            No tenant backup records found matching search or filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 14px 20px; background: #f8fafc; border-top: 1px solid var(--border-color); font-size: 13px;">
            <div style="color: var(--text-muted); font-weight: 500;">
                Showing 1 to {{ count($tenantBackupData) }} of {{ count($tenantBackupData) }} tenant backup environments
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <button class="btn-action-secondary" style="padding: 4px 10px; font-size: 12px;" disabled>Previous</button>
                <button class="btn-action-primary" style="padding: 4px 10px; font-size: 12px;">1</button>
                <button class="btn-action-secondary" style="padding: 4px 10px; font-size: 12px;" disabled>Next</button>
            </div>
        </div>
    </div>

    <!-- 6. BACKUP DETAILS DRAWER -->
    <div class="drawer-overlay" id="backupDetailsDrawer">
        <div class="drawer-panel" id="drawerPanel">
            <div class="drawer-header">
                <div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div id="drawerLogoBox" style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #2563eb, #3b82f6); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 17px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); overflow: hidden; flex-shrink: 0;">
                            <i class="fas fa-database"></i>
                        </div>
                        <div>
                            <h3 id="drawerCompanyName" style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0;">Company Name</h3>
                            <div style="font-size: 12px; color: var(--text-subtle); margin-top: 2px;" id="drawerTenantId">Tenant ID: TEN-001</div>
                        </div>
                    </div>
                </div>
                <button class="btn-action-secondary" id="closeDrawerBtn" style="padding: 8px 12px; border: none; box-shadow: none; border-radius: 50%; background: #ffffff;">
                    <i class="fas fa-xmark" style="font-size: 18px;"></i>
                </button>
            </div>

            <div class="drawer-body">
                <!-- DATABASE OVERVIEW SUMMARY -->
                <div style="background: #f8fafc; border-radius: 14px; border: 1px solid var(--border-color); padding: 18px; box-shadow: var(--shadow-xs);">
                    <div style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px;">
                        DATABASE CONNECTION &amp; SPECS
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <tr>
                            <td style="padding: 6px 0; color: var(--text-subtle); width: 30%;">Database:</td>
                            <td style="padding: 6px 0; font-weight: 700; font-family: monospace; color: var(--text-main);" id="drawerDbName">pms_main</td>
                            <td style="padding: 6px 0; color: var(--text-subtle); width: 25%;">Connection:</td>
                            <td style="padding: 6px 0; font-weight: 700; color: var(--success);" id="drawerConnStatus">● Connected</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: var(--text-subtle);">Database Size:</td>
                            <td style="padding: 6px 0; font-weight: 700; color: var(--text-main);" id="drawerDbSize">5.8 MB</td>
                            <td style="padding: 6px 0; color: var(--text-subtle);">Health:</td>
                            <td style="padding: 6px 0; font-weight: 700; color: var(--primary);" id="drawerHealthBadge">✓ Verified</td>
                        </tr>
                    </table>
                </div>

                <!-- BACKUP INFORMATION CARD -->
                <div style="border: 1px solid var(--border-color); border-radius: 14px; background: #ffffff; padding: 18px; box-shadow: var(--shadow-xs);">
                    <div style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px;">
                        BACKUP ARCHIVE INFORMATION
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                        <tr style="border-bottom: 1px solid var(--border-subtle);">
                            <td style="padding: 8px 0; color: var(--text-muted); font-weight: 500;">Last Backup Run</td>
                            <td style="padding: 8px 0; text-align: right; font-weight: 700; color: var(--text-main);" id="drawerLastBackup">10 min ago</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-subtle);">
                            <td style="padding: 8px 0; color: var(--text-muted); font-weight: 500;">Backup Size</td>
                            <td style="padding: 8px 0; text-align: right; font-weight: 700; font-family: monospace;" id="drawerBackupSize">3.4 MB</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-subtle);">
                            <td style="padding: 8px 0; color: var(--text-muted); font-weight: 500;">Backup Type</td>
                            <td style="padding: 8px 0; text-align: right; font-weight: 700; color: var(--primary);">Full Database Export</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: var(--text-muted); font-weight: 500;">Archive Filename</td>
                            <td style="padding: 8px 0; text-align: right; font-family: monospace; font-size: 11px; color: var(--text-subtle);" id="drawerFileName">backup_pms_main.sql</td>
                        </tr>
                    </table>
                </div>

                <!-- SCHEDULE & RETENTION POLICY -->
                <div style="border: 1px solid var(--border-color); border-radius: 14px; background: #ffffff; padding: 18px; box-shadow: var(--shadow-xs);">
                    <div style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px;">
                        BACKUP SCHEDULE &amp; RETENTION
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                        <tr style="border-bottom: 1px solid var(--border-subtle);">
                            <td style="padding: 8px 0; color: var(--text-muted); font-weight: 500;">Frequency</td>
                            <td style="padding: 8px 0; text-align: right; font-weight: 700; color: var(--text-main);">Daily Automated</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-subtle);">
                            <td style="padding: 8px 0; color: var(--text-muted); font-weight: 500;">Execution Time</td>
                            <td style="padding: 8px 0; text-align: right; font-weight: 700; color: var(--text-main);">02:00 AM UTC</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-subtle);">
                            <td style="padding: 8px 0; color: var(--text-muted); font-weight: 500;">Retention Policy</td>
                            <td style="padding: 8px 0; text-align: right; font-weight: 700; color: var(--text-main);">30 Days Snapshot</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: var(--text-muted); font-weight: 500;">Next Scheduled Run</td>
                            <td style="padding: 8px 0; text-align: right; font-weight: 700; color: var(--success);">Tomorrow, 02:00 AM</td>
                        </tr>
                    </table>
                </div>

                <!-- BACKUP STORAGE PROGRESS -->
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 14px; padding: 18px; box-shadow: var(--shadow-xs);">
                    <div style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 10px;">
                        BACKUP STORAGE CAPACITY
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 6px;">
                        <span>Allocated Storage</span>
                        <span style="color: var(--primary);">14.2 MB / 100 GB</span>
                    </div>
                    <div style="height: 8px; border-radius: 10px; background: #e2e8f0; overflow: hidden;">
                        <div style="height: 100%; width: 5%; background: var(--primary); border-radius: 10px;"></div>
                    </div>
                </div>
            </div>

            <div class="drawer-footer" style="flex-wrap: wrap;">
                <button class="btn-action-secondary" id="drawerVerifyBtn">
                    <i class="fas fa-shield-check"></i> Verify
                </button>
                <button class="btn-action-secondary" id="drawerDownloadBtn">
                    <i class="fas fa-download"></i> Download
                </button>
                <button class="btn-action-secondary" id="drawerLogsBtn">
                    <i class="fas fa-terminal"></i> Logs
                </button>
                <button class="btn-action-secondary" id="drawerRestoreBtn" style="color: var(--danger); border-color: #fecaca; background: #fef2f2;">
                    <i class="fas fa-rotate-left"></i> Restore
                </button>
                <button class="btn-action-primary" id="drawerRunBackupBtn">
                    <i class="fas fa-play"></i> Run Backup
                </button>
            </div>
        </div>
    </div>

    <!-- 7. CREATE BACKUP MODAL -->
    <div class="modal-overlay" id="createBackupModal">
        <div class="modal-box">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 17px; font-weight: 800; color: var(--text-main); margin: 0;"><i class="fas fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i> Create Tenant Backup</h3>
                <button class="btn-action-secondary" id="closeCreateBackupModalBtn" style="padding: 4px 8px; border: none;"><i class="fas fa-xmark"></i></button>
            </div>

            <form id="createBackupForm">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Select Tenant Company</label>
                    <select class="filter-select" id="createBackupCompanySelect" style="width: 100%;">
                        <option value="">-- Choose Tenant Company --</option>
                        @foreach($tenantBackupData as $tComp)
                            <option value="{{ $tComp['company_id'] }}">{{ $tComp['name'] }} ({{ $tComp['db_name'] }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Backup Type</label>
                    <div style="display: flex; gap: 16px; font-size: 13px; font-weight: 600;">
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="radio" name="backup_type" value="full" checked /> Full Database Export
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="radio" name="backup_type" value="incremental" /> Incremental Schema
                        </label>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Description / Memo (Optional)</label>
                    <input type="text" id="createBackupDescription" placeholder="e.g. Pre-upgrade maintenance backup" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; outline: none;" />
                </div>

                <!-- Execution Progress Console -->
                <div id="backupExecutionConsole" style="display: none; margin-bottom: 20px; background: #0f172a; color: #38bdf8; padding: 14px; border-radius: 10px; font-family: monospace; font-size: 12px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px solid #1e293b; padding-bottom: 6px; font-weight: 700; color: #ffffff;">
                        <span><i class="fas fa-spinner fa-spin"></i> EXECUTING BACKUP PROCESS</span>
                        <span id="backupProgressPct">0%</span>
                    </div>
                    <div id="backupConsoleLogs" style="display: flex; flex-direction: column; gap: 4px;">
                        <div>⟳ Initializing database connection...</div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-action-secondary" id="cancelCreateBackupBtn">Cancel</button>
                    <button type="submit" class="btn-action-primary" id="confirmCreateBackupBtn">
                        <i class="fas fa-play"></i> Start Backup Process
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 8. RESTORE BACKUP CONFIRMATION MODAL (HIGH RISK PROTECTION) -->
    <div class="modal-overlay" id="restoreBackupModal">
        <div class="modal-box" style="border-top: 4px solid var(--danger);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <h3 style="font-size: 17px; font-weight: 800; color: var(--danger); margin: 0;">
                    <i class="fas fa-triangle-exclamation"></i> Restore Tenant Database
                </h3>
                <button class="btn-action-secondary" id="closeRestoreModalBtn" style="padding: 4px 8px; border: none;"><i class="fas fa-xmark"></i></button>
            </div>

            <!-- WARNING NOTICE -->
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 14px; margin-bottom: 16px; font-size: 13px; color: #991b1b;">
                <strong>CRITICAL SAFETY WARNING:</strong> This operation will overwrite the current active database schema and data records for tenant <strong id="restoreModalCompanyName">Company</strong> with data from backup file <code id="restoreModalFileName" style="font-size: 11px;">backup.sql</code>.
            </div>

            <div style="font-size: 13px; color: var(--text-main); margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>Target Database:</span>
                    <strong style="font-family: monospace;" id="restoreModalDbName">pms_db</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Backup Created:</span>
                    <strong id="restoreModalDate">14 Aug 2026 10:32 AM</strong>
                </div>
            </div>

            <!-- CONFIRMATION CHECKBOX -->
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 12.5px; font-weight: 700; color: var(--text-main); cursor: pointer; background: #fffbeb; border: 1px solid #fde68a; padding: 12px; border-radius: 8px;">
                    <input type="checkbox" id="confirmRestoreCheckbox" style="margin-top: 2px; cursor: pointer;" />
                    <span>I explicitly understand and acknowledge that this operation will overwrite current tenant data with historical backup data.</span>
                </label>
            </div>

            <!-- RESTORE PROGRESS CONSOLE -->
            <div id="restoreExecutionConsole" style="display: none; margin-bottom: 20px; background: #0f172a; color: #f87171; padding: 14px; border-radius: 10px; font-family: monospace; font-size: 12px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px solid #1e293b; padding-bottom: 6px; font-weight: 700; color: #ffffff;">
                    <span><i class="fas fa-spinner fa-spin"></i> RESTORING DATABASE DATA</span>
                </div>
                <div id="restoreConsoleLogs" style="display: flex; flex-direction: column; gap: 4px;">
                    <div>⟳ Disabling foreign key constraints...</div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button class="btn-action-secondary" id="cancelRestoreBtn">Cancel</button>
                <button class="btn-action-primary" id="confirmRestoreBtn" style="background: var(--danger);" disabled>
                    <i class="fas fa-rotate-left"></i> Execute Restore
                </button>
            </div>
        </div>
    </div>

    <!-- 9. TERMINAL LOG VIEWER MODAL -->
    <div class="modal-overlay" id="backupLogsModal">
        <div class="modal-box" style="max-width: 650px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: var(--text-main); margin: 0;" id="logsModalTitle">BACKUP EXECUTION LOG</h3>
                    <div style="font-size: 12px; color: var(--text-subtle);" id="logsModalSubtitle">Tenant Execution Transcript</div>
                </div>
                <button class="btn-action-secondary" id="closeLogsModalBtn" style="padding: 4px 8px; border: none;"><i class="fas fa-xmark"></i></button>
            </div>

            <div class="log-terminal" id="logsTerminalBody">
                <div>[10:32:01] INFO  Connecting to tenant database...</div>
                <div>[10:32:03] SUCCESS Database connection established.</div>
                <div>[10:32:15] SUCCESS Exporting schema & data tables...</div>
                <div>[10:32:28] SUCCESS Compression completed. Archive created.</div>
                <div>[10:32:30] SUCCESS Integrity verification passed.</div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px;">
                <div style="font-size: 12px; font-weight: 700; color: var(--success);" id="logsStatusBanner">STATUS: SUCCESS</div>
                <div style="display: flex; gap: 8px;">
                    <button class="btn-action-secondary" id="copyLogsBtn"><i class="fas fa-copy"></i> Copy Logs</button>
                    <button class="btn-action-secondary" id="downloadLogsBtn"><i class="fas fa-download"></i> Download</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 10. FLOATING CONTEXTUAL BULK ACTION BAR -->
    <div class="bulk-action-bar" id="bulkActionBar">
        <div style="font-size: 13px; font-weight: 700;">
            <span id="bulkSelectedCount">0</span> tenant environments selected
        </div>
        <div style="height: 16px; width: 1px; background: rgba(255, 255, 255, 0.2);"></div>
        <button class="btn-action-primary" id="triggerBulkBackupBtn" style="padding: 6px 14px; font-size: 12px;">
            <i class="fas fa-play"></i> Run Bulk Backup
        </button>
        <button class="btn-action-secondary" id="clearBulkSelectionBtn" style="padding: 6px 12px; font-size: 12px; background: rgba(255, 255, 255, 0.1); color: #ffffff; border: none;">
            Clear
        </button>
    </div>

    <!-- 11. HISTORICAL BACKUP LOGS SECTION -->
    <div id="backupHistorySection" style="margin-top: 32px; background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 20px 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--text-main); margin: 0;"><i class="fas fa-history" style="color: var(--primary); margin-right: 8px;"></i> HISTORICAL BACKUP ARCHIVES</h3>
                <div style="font-size: 12px; color: var(--text-muted);">Complete record of generated backup files on server storage</div>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase;">Backup Filename</th>
                        <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase;">Database Name</th>
                        <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase;">Size</th>
                        <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase;">Created At</th>
                        <th style="padding: 10px 14px; text-align: center; font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase;">Status</th>
                        <th style="padding: 10px 14px; text-align: right; font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historyList as $hItem)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 10px 14px; font-family: monospace; font-size: 12px; font-weight: 700; color: var(--text-main);">
                            <i class="fas fa-file-code" style="color: var(--primary); margin-right: 6px;"></i> {{ $hItem['filename'] }}
                        </td>
                        <td style="padding: 10px 14px; font-family: monospace; color: var(--text-muted);">
                            {{ $hItem['db_name'] }}
                        </td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 700;">
                            {{ $hItem['size_formatted'] }}
                        </td>
                        <td style="padding: 10px 14px; color: var(--text-muted);">
                            {{ $hItem['created_at'] }} ({{ $hItem['created_ago'] }})
                        </td>
                        <td style="padding: 10px 14px; text-align: center;">
                            @if($hItem['is_valid'])
                                <span style="color: var(--success); font-weight: 700; font-size: 11px;">✓ Verified</span>
                            @else
                                <span style="color: var(--danger); font-weight: 700; font-size: 11px;">✖ Empty/Error</span>
                            @endif
                        </td>
                        <td style="padding: 10px 14px; text-align: right;">
                            <a href="{{ route('super-admin.backups.download', $hItem['filename']) }}" class="btn-action-secondary" style="padding: 4px 8px; font-size: 11px;">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-subtle);">
                            No historical backup archives stored in storage/app/backups yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- JAVASCRIPT CONTROL LOGIC --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF Setup
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // 1. Search & Filter Functionality
    const searchInput = document.getElementById('backupSearchInput');
    const filterStatus = document.getElementById('filterStatus');
    const filterTenant = document.getElementById('filterTenant');
    const resetBtn = document.getElementById('resetFiltersBtn');
    const tableRows = document.querySelectorAll('.backup-table-row');

    function applyFilters() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const statusVal = filterStatus ? filterStatus.value : 'all';
        const tenantVal = filterTenant ? filterTenant.value : 'all';

        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.getAttribute('data-status');
            const rowCompanyId = row.getAttribute('data-company-id');

            const matchesSearch = !query || text.includes(query);
            const matchesStatus = statusVal === 'all' || rowStatus === statusVal;
            const matchesTenant = tenantVal === 'all' || rowCompanyId === tenantVal;

            if (matchesSearch && matchesStatus && matchesTenant) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (filterStatus) filterStatus.addEventListener('change', applyFilters);
    if (filterTenant) filterTenant.addEventListener('change', applyFilters);

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (filterStatus) filterStatus.value = 'all';
            if (filterTenant) filterTenant.value = 'all';
            applyFilters();
        });
    }

    // 2. Refresh Button
    const refreshStatusBtn = document.getElementById('refreshStatusBtn');
    if (refreshStatusBtn) {
        refreshStatusBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }

    // 3. Multi-Select & Bulk Actions
    const selectAllCheckbox = document.getElementById('selectAllBackupsCheckbox');
    const rowCheckboxes = document.querySelectorAll('.row-backup-checkbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const bulkCountSpan = document.getElementById('bulkSelectedCount');
    const clearBulkBtn = document.getElementById('clearBulkSelectionBtn');
    const triggerBulkBackupBtn = document.getElementById('triggerBulkBackupBtn');

    function updateBulkBar() {
        const checked = Array.from(rowCheckboxes).filter(cb => cb.checked);
        if (checked.length > 0) {
            bulkCountSpan.textContent = checked.length;
            bulkActionBar.classList.add('visible');
        } else {
            bulkActionBar.classList.remove('visible');
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateBulkBar();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    if (clearBulkBtn) {
        clearBulkBtn.addEventListener('click', function() {
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            rowCheckboxes.forEach(cb => cb.checked = false);
            updateBulkBar();
        });
    }

    if (triggerBulkBackupBtn) {
        triggerBulkBackupBtn.addEventListener('click', function() {
            const selectedIds = Array.from(rowCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            if (selectedIds.length === 0) return;

            if (!confirm(`Are you sure you want to run backup operations for ${selectedIds.length} selected tenant environments?`)) {
                return;
            }

            triggerBulkBackupBtn.disabled = true;
            triggerBulkBackupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Backing up...';

            fetch("{{ Route::has('super-admin.backups.bulk-create') ? route('super-admin.backups.bulk-create') : (Route::has('superadmin.backups.bulk-create') ? route('superadmin.backups.bulk-create') : url('/super-admin/backups/bulk-create')) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ company_ids: selectedIds })
            })
            .then(res => res.json())
            .then(data => {
                triggerBulkBackupBtn.disabled = false;
                triggerBulkBackupBtn.innerHTML = '<i class="fas fa-play"></i> Run Bulk Backup';
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Bulk backup failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                triggerBulkBackupBtn.disabled = false;
                triggerBulkBackupBtn.innerHTML = '<i class="fas fa-play"></i> Run Bulk Backup';
                alert('Request error: ' + err.message);
            });
        });
    }

    // 4. Slide-Over Details Drawer
    const drawerOverlay = document.getElementById('backupDetailsDrawer');
    const drawerPanel = document.getElementById('drawerPanel');
    const closeDrawerBtn = document.getElementById('closeDrawerBtn');
    const openDrawerBtns = document.querySelectorAll('.open-drawer-btn');

    let currentDrawerCompanyId = null;
    let currentDrawerFileName = null;

    openDrawerBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            currentDrawerCompanyId = this.getAttribute('data-id');
            const compName = this.getAttribute('data-name');
            const compCode = this.getAttribute('data-code');
            const dbName = this.getAttribute('data-db');
            const lastBackup = this.getAttribute('data-last');
            const size = this.getAttribute('data-size');
            currentDrawerFileName = this.getAttribute('data-file');

            const logoUrl = this.getAttribute('data-logo');
            const drawerLogoBox = document.getElementById('drawerLogoBox');
            if (drawerLogoBox) {
                if (logoUrl && logoUrl.trim() !== '') {
                    drawerLogoBox.innerHTML = `<img src="${logoUrl}" alt="${compName}" style="width: 100%; height: 100%; object-fit: cover;" />`;
                } else {
                    drawerLogoBox.innerHTML = `<i class="fas fa-database"></i>`;
                }
            }

            document.getElementById('drawerCompanyName').textContent = compName;
            document.getElementById('drawerTenantId').textContent = 'Tenant ID: ' + compCode;
            document.getElementById('drawerDbName').textContent = dbName;
            document.getElementById('drawerLastBackup').textContent = lastBackup;
            document.getElementById('drawerBackupSize').textContent = size;
            document.getElementById('drawerFileName').textContent = currentDrawerFileName || ('backup_' + dbName + '.sql');

            drawerOverlay.classList.add('open');
            drawerPanel.classList.add('open');
        });
    });

    if (closeDrawerBtn && drawerOverlay) {
        closeDrawerBtn.addEventListener('click', function() {
            drawerOverlay.classList.remove('open');
            drawerPanel.classList.remove('open');
        });
        drawerOverlay.addEventListener('click', function(e) {
            if (e.target === drawerOverlay) {
                drawerOverlay.classList.remove('open');
                drawerPanel.classList.remove('open');
            }
        });
    }

    // 5. Drawer Actions Integration
    const drawerRunBackupBtn = document.getElementById('drawerRunBackupBtn');
    if (drawerRunBackupBtn) {
        drawerRunBackupBtn.addEventListener('click', function() {
            if (currentDrawerCompanyId) {
                runSingleBackupApi(currentDrawerCompanyId);
            }
        });
    }

    const drawerVerifyBtn = document.getElementById('drawerVerifyBtn');
    if (drawerVerifyBtn) {
        drawerVerifyBtn.addEventListener('click', function() {
            if (!currentDrawerFileName) {
                alert('No backup file available to verify.');
                return;
            }

            fetch("{{ Route::has('super-admin.backups.verify') ? route('super-admin.backups.verify') : (Route::has('superadmin.backups.verify') ? route('superadmin.backups.verify') : url('/super-admin/backups/verify')) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ filename: currentDrawerFileName })
            })
            .then(res => res.json())
            .then(data => {
                alert(`Backup Verification Result:\n\nStatus: ${data.status}\nIntegrity: ${data.integrity}\nDetails: ${data.message}`);
            })
            .catch(err => alert('Verification error: ' + err.message));
        });
    }

    const drawerDownloadBtn = document.getElementById('drawerDownloadBtn');
    if (drawerDownloadBtn) {
        drawerDownloadBtn.addEventListener('click', function() {
            if (!currentDrawerFileName) {
                alert('No backup archive file available to download.');
                return;
            }
            window.location.href = "/super-admin/backups/download/" + currentDrawerFileName;
        });
    }

    // 6. Create Backup Modal Logic
    const createBackupModal = document.getElementById('createBackupModal');
    const openCreateBackupBtn = document.getElementById('openCreateBackupBtn');
    const closeCreateBackupModalBtn = document.getElementById('closeCreateBackupModalBtn');
    const cancelCreateBackupBtn = document.getElementById('cancelCreateBackupBtn');
    const createBackupForm = document.getElementById('createBackupForm');
    const singleBackupBtns = document.querySelectorAll('.trigger-single-backup-btn');

    if (openCreateBackupBtn) {
        openCreateBackupBtn.addEventListener('click', function() {
            createBackupModal.classList.add('open');
        });
    }

    if (closeCreateBackupModalBtn) closeCreateBackupModalBtn.addEventListener('click', () => createBackupModal.classList.remove('open'));
    if (cancelCreateBackupBtn) cancelCreateBackupBtn.addEventListener('click', () => createBackupModal.classList.remove('open'));

    singleBackupBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const compId = this.getAttribute('data-id');
            runSingleBackupApi(compId);
        });
    });

    if (createBackupForm) {
        createBackupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const compId = document.getElementById('createBackupCompanySelect').value;
            if (!compId) {
                alert('Please select a tenant company first.');
                return;
            }
            runSingleBackupApi(compId);
        });
    }

    function runSingleBackupApi(companyId) {
        const consoleBox = document.getElementById('backupExecutionConsole');
        const consoleLogs = document.getElementById('backupConsoleLogs');
        const confirmBtn = document.getElementById('confirmCreateBackupBtn');

        if (consoleBox) consoleBox.style.display = 'block';
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Executing...';
        }

        if (consoleLogs) {
            consoleLogs.innerHTML = `
                <div>⟳ Connecting to tenant MySQL database...</div>
                <div>⟳ Initializing table dumper...</div>
            `;
        }

        fetch("{{ Route::has('super-admin.backups.create') ? route('super-admin.backups.create') : (Route::has('superadmin.backups.create') ? route('superadmin.backups.create') : url('/super-admin/backups/create')) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ company_id: companyId })
        })
        .then(res => res.json())
        .then(data => {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-play"></i> Start Backup Process';
            }

            if (data.success) {
                if (consoleLogs) {
                    consoleLogs.innerHTML += `
                        <div style="color: #4ade80;">✓ Data export completed (${data.backup_size}).</div>
                        <div style="color: #4ade80;">✓ Backup file stored: ${data.filename}</div>
                        <div style="color: #4ade80;">✔ ${data.message}</div>
                    `;
                }
                setTimeout(() => {
                    alert(data.message);
                    window.location.reload();
                }, 800);
            } else {
                alert('Backup failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-play"></i> Start Backup Process';
            }
            alert('Execution error: ' + err.message);
        });
    }

    // 7. Restore Modal Safety Protection Flow
    const restoreModal = document.getElementById('restoreBackupModal');
    const closeRestoreModalBtn = document.getElementById('closeRestoreModalBtn');
    const cancelRestoreBtn = document.getElementById('cancelRestoreBtn');
    const confirmRestoreCheckbox = document.getElementById('confirmRestoreCheckbox');
    const confirmRestoreBtn = document.getElementById('confirmRestoreBtn');
    const drawerRestoreBtn = document.getElementById('drawerRestoreBtn');

    if (drawerRestoreBtn) {
        drawerRestoreBtn.addEventListener('click', function() {
            if (!currentDrawerCompanyId || !currentDrawerFileName) {
                alert('No backup archive file selected for restore.');
                return;
            }

            document.getElementById('restoreModalCompanyName').textContent = document.getElementById('drawerCompanyName').textContent;
            document.getElementById('restoreModalDbName').textContent = document.getElementById('drawerDbName').textContent;
            document.getElementById('restoreModalFileName').textContent = currentDrawerFileName;
            document.getElementById('restoreModalDate').textContent = document.getElementById('drawerLastBackup').textContent;

            if (confirmRestoreCheckbox) confirmRestoreCheckbox.checked = false;
            if (confirmRestoreBtn) confirmRestoreBtn.disabled = true;

            restoreModal.classList.add('open');
        });
    }

    if (closeRestoreModalBtn) closeRestoreModalBtn.addEventListener('click', () => restoreModal.classList.remove('open'));
    if (cancelRestoreBtn) cancelRestoreBtn.addEventListener('click', () => restoreModal.classList.remove('open'));

    if (confirmRestoreCheckbox) {
        confirmRestoreCheckbox.addEventListener('change', function() {
            if (confirmRestoreBtn) {
                confirmRestoreBtn.disabled = !confirmRestoreCheckbox.checked;
            }
        });
    }

    if (confirmRestoreBtn) {
        confirmRestoreBtn.addEventListener('click', function() {
            if (!currentDrawerCompanyId || !currentDrawerFileName) return;

            const consoleBox = document.getElementById('restoreExecutionConsole');
            const consoleLogs = document.getElementById('restoreConsoleLogs');

            if (consoleBox) consoleBox.style.display = 'block';
            confirmRestoreBtn.disabled = true;
            confirmRestoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restoring...';

            if (consoleLogs) {
                consoleLogs.innerHTML = `
                    <div>⟳ Connecting to tenant database...</div>
                    <div>⟳ Disabling foreign key constraints...</div>
                    <div>⟳ Executing SQL dump statements...</div>
                `;
            }

            fetch("{{ Route::has('super-admin.backups.restore') ? route('super-admin.backups.restore') : (Route::has('superadmin.backups.restore') ? route('superadmin.backups.restore') : url('/super-admin/backups/restore')) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    company_id: currentDrawerCompanyId,
                    filename: currentDrawerFileName
                })
            })
            .then(res => res.json())
            .then(data => {
                confirmRestoreBtn.disabled = false;
                confirmRestoreBtn.innerHTML = '<i class="fas fa-rotate-left"></i> Execute Restore';

                if (data.success) {
                    if (consoleLogs) {
                        consoleLogs.innerHTML += `
                            <div style="color: #4ade80;">✓ Schema & data tables restored (${data.duration}).</div>
                            <div style="color: #4ade80;">✔ ${data.message}</div>
                        `;
                    }
                    setTimeout(() => {
                        alert(data.message);
                        window.location.reload();
                    }, 800);
                } else {
                    alert('Restore failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                confirmRestoreBtn.disabled = false;
                confirmRestoreBtn.innerHTML = '<i class="fas fa-rotate-left"></i> Execute Restore';
                alert('Restore execution error: ' + err.message);
            });
        });
    }

    // 8. Log Viewer Modal
    const logsModal = document.getElementById('backupLogsModal');
    const closeLogsModalBtn = document.getElementById('closeLogsModalBtn');
    const drawerLogsBtn = document.getElementById('drawerLogsBtn');
    const copyLogsBtn = document.getElementById('copyLogsBtn');

    if (drawerLogsBtn) {
        drawerLogsBtn.addEventListener('click', function() {
            if (!currentDrawerCompanyId) return;

            fetch("/super-admin/backups/logs/" + currentDrawerCompanyId)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const logsBody = document.getElementById('logsTerminalBody');
                        if (logsBody && data.logs) {
                            logsBody.innerHTML = data.logs.map(l => `<div>[${l.timestamp}] ${l.status}  ${l.message}</div>`).join('');
                        }
                        logsModal.classList.add('open');
                    }
                })
                .catch(err => alert('Error fetching logs: ' + err.message));
        });
    }

    if (closeLogsModalBtn) closeLogsModalBtn.addEventListener('click', () => logsModal.classList.remove('open'));

    if (copyLogsBtn) {
        copyLogsBtn.addEventListener('click', function() {
            const logsBody = document.getElementById('logsTerminalBody');
            if (logsBody) {
                navigator.clipboard.writeText(logsBody.innerText);
                alert('Execution logs copied to clipboard.');
            }
        });
    }

    // 9. Export CSV Button
    const exportBtn = document.getElementById('exportBackupsBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            let csv = "Company,Tenant ID,Database,Status,Last Backup,Backup Size,Next Backup\n";
            tableRows.forEach(row => {
                if (row.style.display !== 'none') {
                    const cols = row.querySelectorAll('td');
                    if (cols.length >= 10) {
                        const comp = cols[1].innerText.replace(/\n/g, ' ').trim();
                        const tenantId = cols[2].innerText.trim();
                        const db = cols[3].innerText.trim();
                        const status = cols[4].innerText.trim();
                        const last = cols[5].innerText.replace(/\n/g, ' ').trim();
                        const size = cols[6].innerText.trim();
                        const next = cols[8].innerText.trim();
                        csv += `"${comp}","${tenantId}","${db}","${status}","${last}","${size}","${next}"\n`;
                    }
                }
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', `tenant_backups_export_${new Date().toISOString().slice(0,10)}.csv`);
            a.click();
        });
    }
});
</script>
@endsection
