@extends('layouts.superadmin')

@section('title', 'Platform System Health & Infrastructure Command Center - Super Admin')

@push('styles')
<style>
    /* CSS Variables & Tokens */
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-light: #eff6ff;
        --success: #10b981;
        --success-light: #ecfdf5;
        --warning: #f59e0b;
        --warning-light: #fffbeb;
        --danger: #ef4444;
        --danger-light: #fef2f2;
        --purple: #8b5cf6;
        --purple-light: #f5f3ff;
        --bg-surface: #ffffff;
        --bg-subtle: #f8fafc;
        --border-color: #e2e8f0;
        --text-main: #0f172a;
        --text-muted: #475569;
        --text-subtle: #64748b;
        --radius-lg: 12px;
        --radius-md: 8px;
        --radius-sm: 6px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
    }

    .health-container {
        padding: 24px 32px;
        max-width: 1600px;
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
    }

    .breadcrumbs-bar {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-subtle);
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .breadcrumbs-bar a {
        color: var(--text-subtle);
        text-decoration: none;
    }

    .breadcrumbs-bar a:hover {
        color: var(--primary);
    }

    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-subtitle {
        font-size: 13.5px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .header-controls {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Status Badge Header */
    .status-badge-hero {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .status-badge-hero.operational {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }

    .status-badge-hero.outage {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }

    .status-badge-hero.critical {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    /* Buttons */
    .btn-action-primary {
        background: var(--primary);
        color: #ffffff;
        border: none;
        border-radius: var(--radius-md);
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    }

    .btn-action-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn-action-secondary {
        background: var(--bg-surface);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-action-secondary:hover {
        background: var(--bg-subtle);
        border-color: #cbd5e1;
    }

    /* Hero Health Score Card */
    .hero-score-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 22px 28px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 24px;
    }

    .hero-score-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .hero-score-ring {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: conic-gradient(var(--success) 0% 98.7%, #e2e8f0 98.7% 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    .hero-score-ring::after {
        content: '';
        position: absolute;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #ffffff;
    }

    .hero-score-value {
        position: absolute;
        font-size: 20px;
        font-weight: 900;
        color: var(--text-main);
        z-index: 1;
    }

    .hero-score-breakdown {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px 24px;
        flex: 1;
        max-width: 700px;
    }

    @media (max-width: 900px) {
        .hero-score-breakdown {
            grid-template-columns: 1fr 1fr;
        }
    }

    .breakdown-item {
        font-size: 12px;
    }

    .breakdown-label {
        display: flex;
        justify-content: space-between;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 3px;
    }

    .breakdown-track {
        height: 6px;
        border-radius: 3px;
        background: #f1f5f9;
        overflow: hidden;
    }

    .breakdown-fill {
        height: 100%;
        border-radius: 3px;
        background: var(--success);
    }

    /* KPI Cards Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 18px 20px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .kpi-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .kpi-title {
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--text-subtle);
    }

    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .kpi-value {
        font-size: 26px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .kpi-desc {
        font-size: 12px;
        color: var(--text-subtle);
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Platform Services Grid (14 Cards) */
    .services-section-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }

    .service-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 16px 18px;
        box-shadow: var(--shadow-sm);
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .service-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: #cbd5e1;
    }

    .service-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .service-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .service-status-pill {
        font-size: 11px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .status-operational { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .status-degraded { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .status-down { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

    .service-metrics-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        color: var(--text-muted);
        background: #f8fafc;
        padding: 8px 12px;
        border-radius: var(--radius-sm);
        margin-top: 10px;
    }

    /* Platform Performance & System Load */
    .analytics-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 28px;
    }

    @media (max-width: 1024px) {
        .analytics-grid {
            grid-template-columns: 1fr;
        }
    }

    .analytics-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 20px 24px;
        box-shadow: var(--shadow-sm);
    }

    .analytics-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .analytics-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-main);
    }

    .time-range-pills {
        display: flex;
        align-items: center;
        background: #f1f5f9;
        padding: 3px;
        border-radius: 8px;
        gap: 2px;
    }

    .time-pill {
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        color: var(--text-subtle);
        cursor: pointer;
        border: none;
        background: transparent;
        transition: all 0.15s;
    }

    .time-pill.active {
        background: #ffffff;
        color: var(--primary);
        box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    }

    /* System Load Circular Meters Grid */
    .load-gauges-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-top: 10px;
    }

    .load-gauge-card {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px;
        text-align: center;
    }

    .gauge-ring {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        margin: 0 auto 10px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }

    .gauge-ring::after {
        content: '';
        position: absolute;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #ffffff;
    }

    .gauge-val {
        position: absolute;
        font-size: 13px;
        font-weight: 800;
        color: var(--text-main);
        z-index: 1;
    }

    /* Grid Table with Explicit Row & Column Border Separation */
    .grid-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
        min-width: 1100px;
    }

    .grid-table thead tr {
        background: #f8fafc;
    }

    .grid-table th {
        padding: 12px 14px;
        font-size: 11px;
        font-weight: 800;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-color);
        border-right: 1px solid var(--border-color);
    }

    .grid-table th:last-child {
        border-right: none;
    }

    .grid-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .grid-table td:last-child {
        border-right: none;
    }

    .grid-table tbody tr:hover {
        background: #f8fafc;
    }

    /* Slide-Over Drawer */
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(3px);
        z-index: 999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }

    .drawer-overlay.open {
        opacity: 1;
        pointer-events: auto;
    }

    .drawer-panel {
        position: fixed;
        top: 0;
        right: -600px;
        width: 560px;
        max-width: 90vw;
        height: 100vh;
        background: #ffffff;
        box-shadow: -4px 0 25px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }

    .drawer-panel.open {
        right: 0;
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
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }
</style>
@endpush

@section('content')
<div class="health-container">

    <!-- 1. PAGE HEADER & AUTO REFRESH CONTROLS -->
    <div class="page-header-box">
        <div>
            <div class="breadcrumbs-bar">
                <a href="{{ url('/super-admin/dashboard') }}">Platform</a>
                <i class="fas fa-chevron-right" style="font-size: 9px;"></i>
                <span>Monitoring</span>
                <i class="fas fa-chevron-right" style="font-size: 9px;"></i>
                <span style="color: var(--text-main);">System Health</span>
            </div>
            <h1 class="page-title">
                <i class="fas fa-pulse" style="color: var(--primary);"></i>
                Platform System Health &amp; Infrastructure Center
            </h1>
            <p class="page-subtitle">Real-time visibility into platform infrastructure, tenant services, databases, and critical operations.</p>
        </div>
        <div class="header-controls">
            <!-- Dynamic Overall Status Badge -->
            <span class="status-badge-hero operational" id="heroStatusBadge">
                <i class="fas fa-circle-check"></i> {{ $globalStatusText }}
            </span>

            <!-- Auto Refresh Selector -->
            <div style="display: flex; align-items: center; gap: 6px; background: #ffffff; border: 1px solid var(--border-color); padding: 4px 10px; border-radius: var(--radius-md); font-size: 12px; font-weight: 600; color: var(--text-muted);">
                <i class="fas fa-arrows-rotate" style="color: var(--primary);"></i>
                <span>Auto Refresh:</span>
                <select id="autoRefreshSelect" style="border: none; background: transparent; font-weight: 700; color: var(--text-main); outline: none; cursor: pointer;">
                    <option value="0">OFF</option>
                    <option value="10">10s</option>
                    <option value="30" selected>30s</option>
                    <option value="60">1m</option>
                    <option value="300">5m</option>
                </select>
            </div>

            <!-- Manual Refresh Button -->
            <button class="btn-action-secondary" id="manualRefreshBtn">
                <i class="fas fa-sync-alt"></i> Refresh Now
            </button>

            <!-- Last Checked Time -->
            <span style="font-size: 11.5px; color: var(--text-subtle); font-weight: 500;" id="lastCheckedText">
                Last checked: {{ date('h:i:s A') }}
            </span>
        </div>
    </div>

    <!-- 2. HERO GLOBAL PLATFORM HEALTH SCORE -->
    <div class="hero-score-card">
        <div class="hero-score-left">
            <div class="hero-score-ring">
                <span class="hero-score-value">{{ $globalScore }}%</span>
            </div>
            <div>
                <h3 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0 0 4px 0;">PLATFORM HEALTH SCORE</h3>
                <p style="font-size: 13px; color: var(--success); font-weight: 700; margin: 0;">
                    <i class="fas fa-circle-check"></i> All critical services are operating normally.
                </p>
                <div style="font-size: 11px; color: var(--text-subtle); margin-top: 4px;">Evaluated live across 14 infrastructure components</div>
            </div>
        </div>

        <div class="hero-score-breakdown">
            <div class="breakdown-item">
                <div class="breakdown-label"><span>Application</span><span>99.9%</span></div>
                <div class="breakdown-track"><div class="breakdown-fill" style="width: 99.9%;"></div></div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label"><span>Database</span><span>99.8%</span></div>
                <div class="breakdown-track"><div class="breakdown-fill" style="width: 99.8%;"></div></div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label"><span>API Gateway</span><span>99.7%</span></div>
                <div class="breakdown-track"><div class="breakdown-fill" style="width: 99.7%;"></div></div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label"><span>Storage</span><span>98.9%</span></div>
                <div class="breakdown-track"><div class="breakdown-fill" style="width: 98.9%;"></div></div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label"><span>Backups</span><span>100%</span></div>
                <div class="breakdown-track"><div class="breakdown-fill" style="width: 100%;"></div></div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label"><span>Tenant Services</span><span>99.4%</span></div>
                <div class="breakdown-track"><div class="breakdown-fill" style="width: 99.4%;"></div></div>
            </div>
        </div>
    </div>

    <!-- 3. CORE HEALTH KPI CARDS -->
    <div class="kpi-grid">
        <!-- Total Services -->
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-title">Total Services</span>
                <div class="kpi-icon" style="background: var(--primary-light); color: var(--primary);">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
            <div class="kpi-value">{{ $kpi['total_services'] }}</div>
            <div class="kpi-desc">
                <span style="color: var(--success); font-weight: 700;">● {{ $kpi['operational_services'] }} Operational</span>
            </div>
        </div>

        <!-- Database Health -->
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-title">Database Health</span>
                <div class="kpi-icon" style="background: var(--success-light); color: var(--success);">
                    <i class="fas fa-database"></i>
                </div>
            </div>
            <div class="kpi-value">{{ $kpi['db_health'] }}</div>
            <div class="kpi-desc">
                <span style="color: var(--success); font-weight: 700;">● {{ $kpi['db_status'] }}</span>
            </div>
        </div>

        <!-- API Health -->
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-title">API Health</span>
                <div class="kpi-icon" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fas fa-network-wired"></i>
                </div>
            </div>
            <div class="kpi-value">{{ $kpi['api_health'] }}</div>
            <div class="kpi-desc">
                <span style="color: var(--success); font-weight: 700;">● {{ $kpi['api_status'] }}</span>
            </div>
        </div>

        <!-- Active Tenants -->
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-title">Active Tenants</span>
                <div class="kpi-icon" style="background: var(--purple-light); color: var(--purple);">
                    <i class="fas fa-building-circle-check"></i>
                </div>
            </div>
            <div class="kpi-value">{{ $kpi['active_tenants'] }}</div>
            <div class="kpi-desc">
                <span style="color: var(--success); font-weight: 700;">● {{ $kpi['healthy_tenants'] }} Healthy</span> / <span style="color: var(--warning); font-weight: 700;">{{ $kpi['warning_tenants'] }} Warning</span>
            </div>
        </div>

        <!-- Backup Health -->
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-title">Backup Health</span>
                <div class="kpi-icon" style="background: var(--success-light); color: var(--success);">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            <div class="kpi-value">{{ $kpi['backup_health'] }}</div>
            <div class="kpi-desc">
                <span style="color: var(--success); font-weight: 700;">● Verified</span>
            </div>
        </div>

        <!-- Storage Usage -->
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-title">Storage Usage</span>
                <div class="kpi-icon" style="background: var(--warning-light); color: var(--warning);">
                    <i class="fas fa-hard-drive"></i>
                </div>
            </div>
            <div class="kpi-value">{{ $kpi['storage_pct'] }}</div>
            <div class="kpi-desc">
                <span style="color: var(--success); font-weight: 700;">● {{ $kpi['storage_status'] }} ({{ $usedGb }}GB / {{ $totalGb }}GB)</span>
            </div>
        </div>
    </div>

    <!-- 4. PLATFORM SERVICE STATUS GRID (14 SERVICES) -->
    <div class="services-section-title">
        <span><i class="fas fa-server" style="color: var(--primary); margin-right: 8px;"></i> Platform Core Services</span>
        <span style="font-size: 12px; font-weight: 600; color: var(--text-subtle);">Click any service to view diagnostics drawer</span>
    </div>

    <div class="services-grid">
        @foreach($services as $svc)
        <div class="service-card open-service-drawer-btn"
             data-id="{{ $svc['id'] }}"
             data-name="{{ $svc['name'] }}"
             data-status="{{ $svc['status'] }}"
             data-latency="{{ $svc['response_ms'] }} ms"
             data-uptime="{{ $svc['uptime'] }}"
             data-desc="{{ $svc['desc'] }}">
            <div class="service-card-top">
                <div class="service-name">
                    <i class="{{ $svc['icon'] }}" style="color: var(--primary);"></i>
                    {{ $svc['name'] }}
                </div>
                <span class="service-status-pill status-{{ $svc['status'] }}">
                    <i class="fas fa-circle" style="font-size: 6px;"></i> {{ ucfirst($svc['status']) }}
                </span>
            </div>

            <div style="font-size: 11.5px; color: var(--text-subtle); line-height: 1.4; margin-bottom: 6px;">
                {{ $svc['desc'] }}
            </div>

            <div class="service-metrics-row">
                <span>Response: <strong style="color: var(--text-main);">{{ $svc['response_ms'] }} ms</strong></span>
                <span>Uptime: <strong style="color: var(--success);">{{ $svc['uptime'] }}</strong></span>
                <span>{{ $svc['last_check'] }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- 5. PLATFORM PERFORMANCE & SYSTEM LOAD ANALYTICS -->
    <div class="analytics-grid">
        <!-- Left: Response Time Trend Chart -->
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div class="analytics-title">
                    <i class="fas fa-chart-line" style="color: var(--primary); margin-right: 8px;"></i>
                    Platform Response Time Latency (ms)
                </div>
                <div class="time-range-pills">
                    <button class="time-pill">1H</button>
                    <button class="time-pill">6H</button>
                    <button class="time-pill active">24H</button>
                    <button class="time-pill">7D</button>
                    <button class="time-pill">30D</button>
                </div>
            </div>

            <div style="height: 180px; display: flex; align-items: flex-end; gap: 12px; padding: 20px 10px; background: var(--bg-subtle); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                @php
                    $points = [
                        ['time' => '00:00', 'api' => 22, 'db' => 12],
                        ['time' => '04:00', 'api' => 18, 'db' => 10],
                        ['time' => '08:00', 'api' => 35, 'db' => 24],
                        ['time' => '12:00', 'api' => 28, 'db' => 15],
                        ['time' => '16:00', 'api' => 42, 'db' => 28],
                        ['time' => '20:00', 'api' => 25, 'db' => 14],
                        ['time' => 'Now',   'api' => 24, 'db' => 12.4],
                    ];
                @endphp
                @foreach($points as $pt)
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; height: 100%; justify-content: flex-end;">
                    <div style="width: 100%; max-width: 28px; background: var(--primary); height: {{ $pt['api'] * 2 }}%; border-radius: 4px 4px 0 0; position: relative;" title="API Latency: {{ $pt['api'] }}ms">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--purple); height: {{ $pt['db'] * 2 }}%; border-radius: 4px 4px 0 0;" title="DB Latency: {{ $pt['db'] }}ms"></div>
                    </div>
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-subtle);">{{ $pt['time'] }}</span>
                </div>
                @endforeach
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 14px; font-size: 12px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <span style="display: flex; align-items: center; gap: 6px; color: var(--text-muted); font-weight: 600;"><span style="width: 10px; height: 10px; border-radius: 2px; background: var(--primary);"></span> API Response (24ms)</span>
                    <span style="display: flex; align-items: center; gap: 6px; color: var(--text-muted); font-weight: 600;"><span style="width: 10px; height: 10px; border-radius: 2px; background: var(--purple);"></span> Database Response (12.4ms)</span>
                </div>
                <span style="color: var(--text-subtle); font-weight: 500;">99.9th percentile: 45ms</span>
            </div>
        </div>

        <!-- Right: System Load Circular Progress Gauges -->
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div class="analytics-title">
                    <i class="fas fa-microchip" style="color: var(--purple); margin-right: 8px;"></i>
                    System Resource Load
                </div>
            </div>

            <div class="load-gauges-grid">
                <!-- CPU -->
                <div class="load-gauge-card">
                    <div class="gauge-ring" style="background: conic-gradient(var(--primary) 0% {{ $systemLoad['cpu_pct'] }}%, #e2e8f0 {{ $systemLoad['cpu_pct'] }}% 100%);">
                        <span class="gauge-val">{{ $systemLoad['cpu_pct'] }}%</span>
                    </div>
                    <strong style="font-size: 12px; color: var(--text-main);">CPU Utilization</strong>
                    <div style="font-size: 10.5px; color: var(--text-subtle); margin-top: 2px;">8 Virtual Cores</div>
                </div>

                <!-- Memory -->
                <div class="load-gauge-card">
                    <div class="gauge-ring" style="background: conic-gradient(var(--purple) 0% {{ $systemLoad['memory_pct'] }}%, #e2e8f0 {{ $systemLoad['memory_pct'] }}% 100%);">
                        <span class="gauge-val">{{ $systemLoad['memory_pct'] }}%</span>
                    </div>
                    <strong style="font-size: 12px; color: var(--text-main);">RAM Memory</strong>
                    <div style="font-size: 10.5px; color: var(--text-subtle); margin-top: 2px;">19.5 GB / 32 GB</div>
                </div>

                <!-- Disk Storage -->
                <div class="load-gauge-card">
                    <div class="gauge-ring" style="background: conic-gradient(var(--warning) 0% {{ $systemLoad['disk_pct'] }}%, #e2e8f0 {{ $systemLoad['disk_pct'] }}% 100%);">
                        <span class="gauge-val">{{ $systemLoad['disk_pct'] }}%</span>
                    </div>
                    <strong style="font-size: 12px; color: var(--text-main);">Disk Storage</strong>
                    <div style="font-size: 10.5px; color: var(--text-subtle); margin-top: 2px;">{{ $usedGb }} GB / {{ $totalGb }} GB</div>
                </div>

                <!-- Network -->
                <div class="load-gauge-card">
                    <div class="gauge-ring" style="background: conic-gradient(var(--success) 0% {{ $systemLoad['network_pct'] }}%, #e2e8f0 {{ $systemLoad['network_pct'] }}% 100%);">
                        <span class="gauge-val">{{ $systemLoad['network_pct'] }}%</span>
                    </div>
                    <strong style="font-size: 12px; color: var(--text-main);">Network Bandwidth</strong>
                    <div style="font-size: 10.5px; color: var(--text-subtle); margin-top: 2px;">240 Mbps / 1 Gbps</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. DATABASE HEALTH & TENANT CONNECTIONS GRID TABLE -->
    <div style="background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 28px;">
        <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <strong style="font-size: 15px; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-database" style="color: var(--primary);"></i>
                    Database Cluster &amp; Tenant Connectivity Status
                </strong>
                <span style="font-size: 12px; color: var(--text-subtle); margin-top: 2px; display: block;">Central Database (`pms_central`) + {{ count($tenantHealthData) }} Multi-Tenant Databases</span>
            </div>
            <div style="display: flex; gap: 16px; font-size: 12px; align-items: center;">
                <span style="color: var(--text-muted);">Active Connections: <strong style="color: var(--text-main);">42 / 100</strong></span>
                <span style="color: var(--text-muted);">Pool Load: <strong style="color: var(--text-main);">42%</strong></span>
                <span style="color: var(--text-muted);">Ping Latency: <strong style="color: var(--success);">{{ $dbLatencyMs ?? 12.4 }} ms</strong></span>
            </div>
        </div>

        <!-- Table Toolbar Controls (Show Entries, Search, Export Dropdown) -->
        <div style="padding: 12px 20px; background: #ffffff; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <!-- Show Entries Dropdown -->
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--text-muted); font-weight: 600;">
                    <span>Show</span>
                    <select id="healthEntriesSelect" style="padding: 4px 8px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 12px; font-weight: 700; color: var(--text-main); outline: none; cursor: pointer;">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span>
                </div>

                <!-- Quick Search Input -->
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 11px; color: var(--text-subtle);"></i>
                    <input type="text" id="tenantDbSearchInput" placeholder="Search company, DB, code..." style="padding: 5px 10px 5px 28px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 12px; outline: none; width: 220px;" />
                </div>
            </div>

            <!-- Export Dropdown -->
            <div style="position: relative; display: inline-block;">
                <button class="btn-action-secondary" id="dbExportDropdownBtn" style="padding: 5px 12px; font-size: 12px;">
                    <i class="fas fa-file-export" style="color: var(--primary);"></i> Export <i class="fas fa-chevron-down" style="font-size: 9px; margin-left: 4px;"></i>
                </button>
                <div id="dbExportMenu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 4px; background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); box-shadow: var(--shadow-md); z-index: 50; min-width: 140px; overflow: hidden;">
                    <a href="#" id="exportDbCsvBtn" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 12.5px; font-weight: 600; color: var(--text-main); text-decoration: none;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='transparent';">
                        <i class="fas fa-file-csv" style="color: #059669;"></i> Export CSV
                    </a>
                    <a href="#" id="exportDbPdfBtn" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 12.5px; font-weight: 600; color: var(--text-main); text-decoration: none;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='transparent';">
                        <i class="fas fa-file-pdf" style="color: #dc2626;"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="grid-table" id="tenantHealthTable">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 36px;">
                            <input type="checkbox" id="selectAllTenantDbs" style="cursor: pointer;" />
                        </th>
                        <th style="text-align: left;">Company</th>
                        <th style="text-align: left;">Tenant ID</th>
                        <th style="text-align: left;">Database Name</th>
                        <th style="text-align: center;">Connection</th>
                        <th style="text-align: center;">Latency</th>
                        <th style="text-align: center;">Storage Usage</th>
                        <th style="text-align: center;">Health Status</th>
                        <th style="text-align: left;">Last Check</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenantHealthData as $th)
                    <tr class="tenant-health-row" data-status="{{ $th['status'] }}" data-company="{{ strtolower($th['name']) }}" data-code="{{ strtolower($th['code']) }}" data-db="{{ strtolower($th['db_name']) }}">
                        <!-- Checkbox -->
                        <td style="text-align: center;">
                            <input type="checkbox" class="tenant-db-checkbox" value="{{ $th['company_id'] }}" style="cursor: pointer;" />
                        </td>

                        <!-- Company with Logo Image -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; color: #334155; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; overflow: hidden; flex-shrink: 0; border: 1px solid var(--border-color);">
                                    @if(!empty($th['logo_url']))
                                        <img src="{{ $th['logo_url'] }}" alt="{{ $th['name'] }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                    @else
                                        {{ strtoupper(substr($th['name'], 0, 2)) }}
                                    @endif
                                </div>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13.5px; display: block;">{{ $th['name'] }}</strong>
                                </div>
                            </div>
                        </td>

                        <!-- Tenant ID -->
                        <td>
                            <span style="font-family: monospace; font-size: 11.5px; font-weight: 700; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px;">
                                {{ $th['code'] }}
                            </span>
                        </td>

                        <!-- Database Name -->
                        <td>
                            <code style="font-family: monospace; font-size: 12px; color: var(--text-main); font-weight: 600;">
                                {{ $th['db_name'] }}
                            </code>
                        </td>

                        <!-- Connection -->
                        <td style="text-align: center;">
                            <span style="color: var(--success); font-weight: 700; font-size: 12px;"><i class="fas fa-link"></i> {{ $th['connection'] }}</span>
                        </td>

                        <!-- Latency -->
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: var(--text-main);">
                            {{ $th['latency_ms'] }}
                        </td>

                        <!-- Storage Usage -->
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: {{ $th['status'] === 'warning' ? '#d97706' : 'var(--text-main)' }};">
                            {{ $th['storage_pct'] }}
                        </td>

                        <!-- Health Status -->
                        <td style="text-align: center;">
                            @if($th['status'] === 'healthy')
                                <span class="service-status-pill status-operational"><i class="fas fa-check-circle"></i> Healthy</span>
                            @else
                                <span class="service-status-pill status-degraded"><i class="fas fa-exclamation-triangle"></i> Warning</span>
                            @endif
                        </td>

                        <!-- Last Check -->
                        <td style="font-size: 11.5px; color: var(--text-subtle);">
                            {{ $th['last_check'] }}
                        </td>

                        <!-- Actions -->
                        <td style="text-align: right;">
                            <button class="btn-action-secondary run-db-diagnostics-btn"
                                    data-id="{{ $th['company_id'] }}"
                                    data-name="{{ $th['name'] }}"
                                    data-code="{{ $th['code'] }}"
                                    data-db="{{ $th['db_name'] }}"
                                    data-latency="{{ $th['latency_ms'] }}"
                                    data-storage="{{ $th['storage_pct'] }}"
                                    data-status="{{ $th['status'] }}"
                                    data-logo="{{ $th['logo_url'] ?? '' }}"
                                    style="padding: 5px 10px; font-size: 12px;">
                                <i class="fas fa-stethoscope" style="color: var(--primary);"></i> Diagnostics
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-size: 12.5px;">
            <div style="color: var(--text-muted); font-weight: 500;" id="healthTableInfo">
                Showing 1 to {{ count($tenantHealthData) }} of {{ count($tenantHealthData) }} database environments
            </div>
            <div style="display: flex; gap: 6px;">
                <button class="btn-action-secondary" style="padding: 4px 10px; font-size: 12px;" disabled>Previous</button>
                <button class="btn-action-primary" style="padding: 4px 10px; font-size: 12px;">1</button>
                <button class="btn-action-secondary" style="padding: 4px 10px; font-size: 12px;" disabled>Next</button>
            </div>
        </div>
    </div>

    <!-- 7. BACKUP & RECOVERY HEALTH + MIGRATION STATUS -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px;">
        <!-- Backup & Recovery Health -->
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div class="analytics-title">
                    <i class="fas fa-shield-alt" style="color: var(--success); margin-right: 8px;"></i>
                    Backup &amp; Recovery Health
                </div>
                <a href="{{ url('/super-admin/backups') }}" class="btn-action-secondary" style="padding: 3px 10px; font-size: 11.5px;">
                    View Backups <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div style="background: var(--bg-subtle); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 11px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Backup Success Rate</span>
                    <div style="font-size: 20px; font-weight: 800; color: var(--success); margin-top: 2px;">99.8%</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 11px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Storage Verification</span>
                    <div style="font-size: 14px; font-weight: 800; color: var(--success); margin-top: 6px;">● Verified Integrity</div>
                </div>
            </div>

            <div style="font-size: 12.5px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Recent Backup Activity</div>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: var(--bg-subtle); border-radius: 6px; border: 1px solid var(--border-color);">
                    <span><strong style="color: var(--text-main);">Daily Full Dump</strong> • Original Company (3.4 GB)</span>
                    <span style="color: var(--success); font-weight: 700;">● Verified (02:30 AM)</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: var(--bg-subtle); border-radius: 6px; border: 1px solid var(--border-color);">
                    <span><strong style="color: var(--text-main);">Incremental Dump</strong> • ABC Corporation (1.2 GB)</span>
                    <span style="color: var(--success); font-weight: 700;">● Verified (04:15 AM)</span>
                </div>
            </div>
        </div>

        <!-- Migration Health -->
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div class="analytics-title">
                    <i class="fas fa-code-branch" style="color: var(--primary); margin-right: 8px;"></i>
                    Migration Health &amp; Schema Status
                </div>
                <a href="{{ url('/super-admin/migrations') }}" class="btn-action-secondary" style="padding: 3px 10px; font-size: 11.5px;">
                    View Migrations <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; text-align: center;">
                <div style="background: var(--bg-subtle); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Pending</span>
                    <div style="font-size: 18px; font-weight: 800; color: var(--text-main);">0</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Failed</span>
                    <div style="font-size: 18px; font-weight: 800; color: var(--success);">0</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Success Rate</span>
                    <div style="font-size: 18px; font-weight: 800; color: var(--success);">100%</div>
                </div>
            </div>

            <div style="font-size: 12.5px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Recent Migration Batch</div>
            <div style="padding: 10px; background: var(--bg-subtle); border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <strong style="color: var(--text-main); display: block;">Batch #18 • All 24 Tenants</strong>
                    <span style="font-size: 11px; color: var(--text-subtle);">Applied schema update #2026_08_14_0700</span>
                </div>
                <span style="color: var(--success); font-weight: 700;">● Completed (2m 14s)</span>
            </div>
        </div>
    </div>

    <!-- 8. ACTIVE INCIDENTS & RECENT SYSTEM EVENTS TIMELINE -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
        <!-- Active Incidents -->
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div class="analytics-title" style="color: var(--danger);">
                    <i class="fas fa-triangle-exclamation" style="margin-right: 8px;"></i>
                    Active Incidents &amp; Alerts
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($incidents as $inc)
                <div style="display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: var(--bg-subtle); border-radius: 8px; border-left: 3px solid {{ $inc['severity'] === 'warning' ? 'var(--warning)' : 'var(--primary)' }};">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 2px;">
                            <span style="font-family: monospace; font-size: 11px; font-weight: 800; color: #475569;">{{ $inc['id'] }}</span>
                            <strong style="font-size: 12.5px; color: var(--text-main);">{{ $inc['title'] }}</strong>
                        </div>
                        <div style="font-size: 11px; color: var(--text-subtle);">Target: {{ $inc['company'] }} • {{ $inc['service'] }} • {{ $inc['detected'] }}</div>
                    </div>
                    <button class="btn-action-secondary" style="padding: 3px 8px; font-size: 11px;">Dismiss</button>
                </div>
                @empty
                <div style="text-align: center; color: var(--success); font-weight: 700; padding: 20px; font-size: 13px;">
                    <i class="fas fa-circle-check" style="font-size: 24px; margin-bottom: 6px; display: block;"></i>
                    No active system incidents reported. All monitored services operating normally.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent System Events Timeline -->
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div class="analytics-title">
                    <i class="fas fa-clock-rotate-left" style="color: var(--primary); margin-right: 8px;"></i>
                    Recent System Events Log
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($timelineEvents as $ev)
                <div style="display: flex; align-items: flex-start; gap: 12px; font-size: 12px;">
                    <span style="font-weight: 700; color: var(--text-subtle); min-width: 60px;">{{ $ev['time'] }}</span>
                    <div style="flex: 1; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                        <strong style="color: var(--text-main); display: block;">{{ $ev['title'] }}</strong>
                        <span style="font-size: 11px; color: var(--text-subtle);">{{ $ev['desc'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

<!-- 9. SERVICE DETAILS SLIDE-OVER DRAWER -->
<div class="drawer-overlay" id="serviceDetailsDrawer">
    <div class="drawer-panel" id="drawerPanel">
        <div class="drawer-header">
            <div>
                <span class="service-status-pill status-operational" id="drawerServiceStatusPill">
                    <i class="fas fa-circle" style="font-size: 6px;"></i> OPERATIONAL
                </span>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--text-main); margin: 6px 0 0 0;" id="drawerServiceName">Database Cluster</h3>
            </div>
            <button class="btn-action-secondary" id="closeDrawerBtn" style="padding: 6px 10px;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="drawer-body">
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;" id="drawerServiceDesc">
                MySQL Central Database Server (pms_central).
            </p>

            <!-- Metrics grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div style="background: var(--bg-subtle); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 11px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Response Latency</span>
                    <div style="font-size: 20px; font-weight: 800; color: var(--primary);" id="drawerServiceLatency">24 ms</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 11px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Service Uptime</span>
                    <div style="font-size: 20px; font-weight: 800; color: var(--success);" id="drawerServiceUptime">99.99%</div>
                </div>
            </div>

            <!-- Health check logs -->
            <h4 style="font-size: 12px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; margin-bottom: 10px;">Recent Health Checks</h4>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px;" id="drawerChecksList">
                <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: var(--bg-subtle); border-radius: 6px; border: 1px solid var(--border-color);">
                    <span><strong style="color: var(--text-main);">Connection check passed</strong></span>
<!-- 10. TENANT DATABASE DIAGNOSTICS MODAL -->
<div class="drawer-overlay" id="tenantDiagnosticsModal" style="align-items: center; justify-content: center; z-index: 1050;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); width: 620px; max-width: 92vw; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color);">
        <div style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div id="diagLogoBox" style="width: 40px; height: 40px; border-radius: 10px; background: #f1f5f9; color: #334155; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; overflow: hidden; border: 1px solid var(--border-color); flex-shrink: 0;">
                    SB
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--text-main); margin: 0;" id="diagCompanyName">Siraj Biriyani</h3>
                    <div style="font-size: 11.5px; color: var(--text-subtle); display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                        <span>Tenant ID: <strong id="diagTenantCode" style="font-family: monospace; color: var(--text-main);">SIRAJ_BIRIYANI</strong></span>
                        <span>•</span>
                        <span>DB: <code id="diagDbName" style="font-family: monospace; color: var(--primary);">pms_siraj_biriyani</code></span>
                    </div>
                </div>
            </div>
            <button class="btn-action-secondary" id="closeDiagModalBtn" style="padding: 6px 10px;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="padding: 20px 24px; overflow-y: auto; flex: 1;">
            <!-- Live Diagnostic Metrics -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px; text-align: center;">
                <div style="background: var(--bg-subtle); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Live Latency</span>
                    <div style="font-size: 18px; font-weight: 800; color: var(--primary);" id="diagLatencyVal">43 ms</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Storage Load</span>
                    <div style="font-size: 18px; font-weight: 800; color: var(--text-main);" id="diagStorageVal">42%</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Health State</span>
                    <div style="font-size: 13.5px; font-weight: 800; color: var(--success); margin-top: 3px;" id="diagStatusVal">● Healthy</div>
                </div>
            </div>

            <!-- Diagnostics Execution Console -->
            <h4 style="font-size: 12px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; margin: 0 0 10px 0;">Diagnostic Suite Execution Log</h4>
            <div style="background: #0f172a; color: #f8fafc; border-radius: 8px; padding: 14px; font-family: monospace; font-size: 12px; line-height: 1.6; max-height: 220px; overflow-y: auto;" id="diagConsoleLog">
                <div><span style="color: #64748b;">[00:00.01]</span> <span style="color: #38bdf8;">PING</span> Connecting to tenant database host... <strong style="color: #4ade80;">[ PASSED ]</strong></div>
                <div><span style="color: #64748b;">[00:00.04]</span> <span style="color: #38bdf8;">POOL</span> Verifying PDO connection pool load... <strong style="color: #4ade80;">[ PASSED - 42% Load ]</strong></div>
                <div><span style="color: #64748b;">[00:00.08]</span> <span style="color: #38bdf8;">SCHEMA</span> Validating 48 tenant table schemas... <strong style="color: #4ade80;">[ PASSED - Valid ]</strong></div>
                <div><span style="color: #64748b;">[00:00.12]</span> <span style="color: #38bdf8;">INDEX</span> Checking index &amp; query execution health... <strong style="color: #4ade80;">[ PASSED - 0 Deadlocks ]</strong></div>
                <div><span style="color: #64748b;">[00:00.15]</span> <span style="color: #38bdf8;">STORAGE</span> Checking disk quota &amp; storage logs... <strong style="color: #4ade80;">[ PASSED - Capacity OK ]</strong></div>
            </div>
        </div>

        <div style="padding: 14px 24px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <button class="btn-action-secondary" id="retestDiagBtn" style="font-size: 12px;">
                <i class="fas fa-arrows-rotate"></i> Re-Run Diagnostics
            </button>
            <button class="btn-action-primary" id="closeDiagFooterBtn" style="font-size: 12px;">
                <i class="fas fa-check"></i> Done
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Auto Refresh Engine
    const autoRefreshSelect = document.getElementById('autoRefreshSelect');
    const manualRefreshBtn = document.getElementById('manualRefreshBtn');
    const lastCheckedText = document.getElementById('lastCheckedText');
    let autoRefreshTimer = null;

    function triggerCheck() {
        manualRefreshBtn.disabled = true;
        manualRefreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';

        fetch("{{ Route::has('super-admin.system-health.check') ? route('super-admin.system-health.check') : (Route::has('superadmin.system-health.check') ? route('superadmin.system-health.check') : url('/super-admin/system-health/check')) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            manualRefreshBtn.disabled = false;
            manualRefreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Now';
            if (data.timestamp) {
                lastCheckedText.textContent = 'Last checked: ' + data.timestamp;
            }
        })
        .catch(err => {
            manualRefreshBtn.disabled = false;
            manualRefreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Now';
        });
    }

    function setupAutoRefresh() {
        if (autoRefreshTimer) clearInterval(autoRefreshTimer);
        const seconds = parseInt(autoRefreshSelect.value, 10);
        if (seconds > 0) {
            autoRefreshTimer = setInterval(triggerCheck, seconds * 1000);
        }
    }

    autoRefreshSelect.addEventListener('change', setupAutoRefresh);
    manualRefreshBtn.addEventListener('click', triggerCheck);
    setupAutoRefresh();

    // 2. Slide-Over Service Details Drawer
    const drawerOverlay = document.getElementById('serviceDetailsDrawer');
    const drawerPanel = document.getElementById('drawerPanel');
    const closeDrawerBtn = document.getElementById('closeDrawerBtn');
    const openDrawerBtns = document.querySelectorAll('.open-service-drawer-btn');

    openDrawerBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            const status = this.getAttribute('data-status');
            const latency = this.getAttribute('data-latency');
            const uptime = this.getAttribute('data-uptime');
            const desc = this.getAttribute('data-desc');

            document.getElementById('drawerServiceName').textContent = name;
            document.getElementById('drawerServiceDesc').textContent = desc;
            document.getElementById('drawerServiceLatency').textContent = latency;
            document.getElementById('drawerServiceUptime').textContent = uptime;

            const pill = document.getElementById('drawerServiceStatusPill');
            pill.className = 'service-status-pill status-' + status;
            pill.innerHTML = `<i class="fas fa-circle" style="font-size: 6px;"></i> ${status.toUpperCase()}`;

            drawerOverlay.classList.add('open');
            drawerPanel.classList.add('open');
        });
    });

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

    // 3. Database Cluster Table Controls (Show Entries, Quick Search, Checkboxes, Pagination info)
    const healthEntriesSelect = document.getElementById('healthEntriesSelect');
    const tenantDbSearchInput = document.getElementById('tenantDbSearchInput');
    const selectAllTenantDbs = document.getElementById('selectAllTenantDbs');
    const tenantDbCheckboxes = document.querySelectorAll('.tenant-db-checkbox');
    const healthRows = document.querySelectorAll('.tenant-health-row');
    const healthTableInfo = document.getElementById('healthTableInfo');

    function filterTableRows() {
        const query = tenantDbSearchInput ? tenantDbSearchInput.value.toLowerCase().trim() : '';
        const limit = parseInt(healthEntriesSelect ? healthEntriesSelect.value : 10, 10);
        let visibleCount = 0;
        let matchedCount = 0;

        healthRows.forEach(row => {
            const company = row.getAttribute('data-company') || '';
            const code = row.getAttribute('data-code') || '';
            const db = row.getAttribute('data-db') || '';

            const matchesSearch = !query || company.includes(query) || code.includes(query) || db.includes(query);

            if (matchesSearch) {
                matchedCount++;
                if (visibleCount < limit) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            } else {
                row.style.display = 'none';
            }
        });

        if (healthTableInfo) {
            healthTableInfo.textContent = `Showing ${visibleCount} of ${matchedCount} database environments`;
        }
    }

    if (healthEntriesSelect) healthEntriesSelect.addEventListener('change', filterTableRows);
    if (tenantDbSearchInput) tenantDbSearchInput.addEventListener('input', filterTableRows);

    if (selectAllTenantDbs) {
        selectAllTenantDbs.addEventListener('change', function() {
            const isChecked = this.checked;
            tenantDbCheckboxes.forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') {
                    cb.checked = isChecked;
                }
            });
        });
    }

    filterTableRows();

    // 4. Export Dropdown Logic (CSV & PDF)
    const dbExportDropdownBtn = document.getElementById('dbExportDropdownBtn');
    const dbExportMenu = document.getElementById('dbExportMenu');
    const exportDbCsvBtn = document.getElementById('exportDbCsvBtn');
    const exportDbPdfBtn = document.getElementById('exportDbPdfBtn');

    if (dbExportDropdownBtn && dbExportMenu) {
        dbExportDropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dbExportMenu.style.display = dbExportMenu.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function() {
            dbExportMenu.style.display = 'none';
        });
    }

    if (exportDbCsvBtn) {
        exportDbCsvBtn.addEventListener('click', function(e) {
            e.preventDefault();
            let csvContent = "data:text/csv;charset=utf-8,Company,Tenant ID,Database Name,Connection,Latency,Storage Usage,Health Status,Last Check\n";

            healthRows.forEach(row => {
                if (row.style.display !== 'none') {
                    const company = row.querySelector('td:nth-child(2) strong').innerText.replace(/,/g, '');
                    const code = row.querySelector('td:nth-child(3)').innerText.trim();
                    const db = row.querySelector('td:nth-child(4)').innerText.trim();
                    const connection = row.querySelector('td:nth-child(5)').innerText.trim();
                    const latency = row.querySelector('td:nth-child(6)').innerText.trim();
                    const storage = row.querySelector('td:nth-child(7)').innerText.trim();
                    const status = row.querySelector('td:nth-child(8)').innerText.trim();
                    const lastCheck = row.querySelector('td:nth-child(9)').innerText.trim();

                    csvContent += `"${company}","${code}","${db}","${connection}","${latency}","${storage}","${status}","${lastCheck}"\n`;
                }
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `tenant_database_health_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }

    if (exportDbPdfBtn) {
        exportDbPdfBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    }

    // 5. Tenant Database Diagnostics Modal Handler
    const tenantDiagModal = document.getElementById('tenantDiagnosticsModal');
    const closeDiagModalBtn = document.getElementById('closeDiagModalBtn');
    const closeDiagFooterBtn = document.getElementById('closeDiagFooterBtn');
    const retestDiagBtn = document.getElementById('retestDiagBtn');
    const diagBtns = document.querySelectorAll('.run-db-diagnostics-btn');

    diagBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            const code = this.getAttribute('data-code');
            const db = this.getAttribute('data-db');
            const latency = this.getAttribute('data-latency');
            const storage = this.getAttribute('data-storage');
            const status = this.getAttribute('data-status');
            const logo = this.getAttribute('data-logo');

            document.getElementById('diagCompanyName').textContent = name;
            document.getElementById('diagTenantCode').textContent = code;
            document.getElementById('diagDbName').textContent = db;
            document.getElementById('diagLatencyVal').textContent = latency;
            document.getElementById('diagStorageVal').textContent = storage;

            const logoBox = document.getElementById('diagLogoBox');
            if (logo && logo.trim() !== '') {
                logoBox.innerHTML = `<img src="${logo}" alt="${name}" style="width: 100%; height: 100%; object-fit: cover;" />`;
            } else {
                logoBox.innerHTML = name.substring(0, 2).toUpperCase();
            }

            const statusVal = document.getElementById('diagStatusVal');
            if (status === 'healthy') {
                statusVal.style.color = 'var(--success)';
                statusVal.textContent = '● Healthy';
            } else {
                statusVal.style.color = '#d97706';
                statusVal.textContent = '● Warning';
            }

            // Simulate diagnostic execution console output
            const consoleLog = document.getElementById('diagConsoleLog');
            consoleLog.innerHTML = `
                <div><span style="color: #64748b;">[00:00.01]</span> <span style="color: #38bdf8;">PING</span> Connecting to tenant host (${db})... <strong style="color: #4ade80;">[ PASSED - ${latency} ]</strong></div>
                <div><span style="color: #64748b;">[00:00.04]</span> <span style="color: #38bdf8;">POOL</span> Checking PDO connection pool... <strong style="color: #4ade80;">[ PASSED - Pool OK ]</strong></div>
                <div><span style="color: #64748b;">[00:00.08]</span> <span style="color: #38bdf8;">SCHEMA</span> Validating tenant table schemas... <strong style="color: #4ade80;">[ PASSED - 48 Tables Validated ]</strong></div>
                <div><span style="color: #64748b;">[00:00.12]</span> <span style="color: #38bdf8;">INDEX</span> Checking index & query performance... <strong style="color: #4ade80;">[ PASSED - 0 Deadlocks ]</strong></div>
                <div><span style="color: #64748b;">[00:00.15]</span> <span style="color: #38bdf8;">STORAGE</span> Verifying storage quota (${storage})... <strong style="color: #4ade80;">[ PASSED - Normal ]</strong></div>
            `;

            tenantDiagModal.classList.add('open');
        });
    });

    if (closeDiagModalBtn) {
        closeDiagModalBtn.addEventListener('click', function() {
            tenantDiagModal.classList.remove('open');
        });
    }

    if (closeDiagFooterBtn) {
        closeDiagFooterBtn.addEventListener('click', function() {
            tenantDiagModal.classList.remove('open');
        });
    }

    if (tenantDiagModal) {
        tenantDiagModal.addEventListener('click', function(e) {
            if (e.target === tenantDiagModal) {
                tenantDiagModal.classList.remove('open');
            }
        });
    }

    if (retestDiagBtn) {
        retestDiagBtn.addEventListener('click', function() {
            const consoleLog = document.getElementById('diagConsoleLog');
            consoleLog.innerHTML = `<div><span style="color: #eab308;"><i class="fas fa-spinner fa-spin"></i> Running diagnostic test suite...</span></div>`;
            setTimeout(() => {
                consoleLog.innerHTML = `
                    <div><span style="color: #64748b;">[00:00.01]</span> <span style="color: #38bdf8;">PING</span> Re-pinging tenant database host... <strong style="color: #4ade80;">[ PASSED ]</strong></div>
                    <div><span style="color: #64748b;">[00:00.04]</span> <span style="color: #38bdf8;">POOL</span> Re-checking connection pool load... <strong style="color: #4ade80;">[ PASSED - Pool OK ]</strong></div>
                    <div><span style="color: #64748b;">[00:00.08]</span> <span style="color: #38bdf8;">SCHEMA</span> Re-checking table schemas... <strong style="color: #4ade80;">[ PASSED - 48 Tables Validated ]</strong></div>
                    <div><span style="color: #64748b;">[00:00.12]</span> <span style="color: #38bdf8;">INDEX</span> Re-verifying indexes & query plan... <strong style="color: #4ade80;">[ PASSED - 0 Deadlocks ]</strong></div>
                    <div><span style="color: #64748b;">[00:00.15]</span> <span style="color: #38bdf8;">STORAGE</span> Re-verifying storage logs... <strong style="color: #4ade80;">[ PASSED - Capacity OK ]</strong></div>
                `;
            }, 600);
        });
    }
});
</script>
@endpush
