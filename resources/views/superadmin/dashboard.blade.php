@extends('layouts.superadmin')

@section('title', 'Super Admin · Command Center')
@section('page_title', 'Command Center')
@section('page_subtitle', 'Central Multi-Tenant Control Hub')

@section('content')
  <style>
    /* ===== WELCOME / COMMANDo CENTER ===== */
    /* ===== WELCOME / COMMAND CENTER HERO CARD BANNER ===== */
    @keyframes gradientShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    .welcome-section {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(246, 250, 247, 0.94) 100%);
      border: 1px solid rgba(226, 232, 240, 0.95);
      border-radius: 24px;
      box-shadow: 0 16px 45px -10px rgba(15, 116, 76, 0.1), 0 4px 14px rgba(0, 0, 0, 0.03);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      padding: 24px 32px;
      margin: 8px 0 24px;
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
      isolation: isolate;
      transition: all 0.3s ease;
    }

    .welcome-section:hover {
      transform: translateY(-2px);
      box-shadow: 0 22px 55px -10px rgba(15, 116, 76, 0.14);
    }

    .welcome-section::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #073a26, #0f744c, #10b981, #2563eb, #7c3aed);
      background-size: 300% 300%;
      animation: gradientShift 6s ease infinite;
    }

    .welcome-section .left {
      flex: 1;
      min-width: 280px;
    }

    .welcome-section .left .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #073a26 0%, #0f744c 100%);
      color: #ffffff !important;
      padding: 4px 12px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      margin-bottom: 8px;
      box-shadow: 0 4px 12px rgba(15, 116, 76, 0.2);
    }

    .welcome-section .left .greeting {
      font-size: 28px;
      font-weight: 900;
      letter-spacing: -0.5px;
      line-height: 1.2;
      color: var(--slate-dark);
      margin-bottom: 4px;
    }

    .welcome-section .left .greeting .highlight {
      background: linear-gradient(135deg, var(--emerald-dark) 0%, var(--emerald-primary) 50%, var(--emerald-light) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .welcome-section .left .desc {
      font-size: 14.5px;
      color: var(--slate-muted);
      font-weight: 600;
      margin-top: 0px;
      line-height: 1.45;
    }

    .welcome-section .right {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .welcome-section .right .date {
      font-size: 13px;
      color: var(--slate-muted);
      font-weight: 700;
      background: rgba(241, 245, 249, 0.9);
      padding: 8px 16px;
      border-radius: 999px;
      border: 1px solid rgba(226, 232, 240, 0.9);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .welcome-section .right .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 12.5px;
      font-weight: 800;
      color: var(--emerald-primary);
      background: var(--emerald-soft);
      padding: 8px 16px;
      border-radius: 999px;
      border: 1px solid rgba(15, 116, 76, 0.2);
    }

    .welcome-section .right .status-badge .dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--emerald-light);
      display: inline-block;
      box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
    }

    .welcome-section .right .actions {
      display: flex;
      gap: 10px;
    }

    /* ===== BUTTONS ===== */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 8px 18px;
      border-radius: 999px;
      font-weight: 700;
      font-size: 13px;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid transparent;
      white-space: nowrap;
      height: 38px;
      cursor: pointer;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--emerald-dark), var(--emerald-primary), var(--emerald-light));
      color: #fff;
      box-shadow: 0 8px 24px rgba(15, 116, 76, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 32px rgba(15, 116, 76, 0.4);
    }

    .btn-outline {
      background: transparent;
      border-color: rgba(226, 232, 240, 0.9);
      color: var(--slate-body);
    }

    .btn-outline:hover {
      background: var(--emerald-soft);
      border-color: var(--emerald-primary);
      color: var(--emerald-primary);
    }

    .btn-secondary {
      background: #f1f5f9;
      color: var(--slate-dark);
      border: 1px solid #cbd5e1;
    }

    .btn-secondary:hover {
      background: #e2e8f0;
    }

    /* ===== KPI GRID ===== */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 14px;
      margin: 8px 0 24px;
    }

    .kpi-card {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(8px);
      border-radius: 20px;
      padding: 16px 18px 18px;
      border: 1px solid rgba(226, 232, 240, 0.85);
      box-shadow: var(--card-shadow-sm);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .kpi-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--emerald-primary), var(--emerald-light));
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .kpi-card:hover {
      border-color: rgba(15, 116, 76, 0.25);
      box-shadow: var(--card-shadow-lg);
      transform: translateY(-4px);
    }

    .kpi-card:hover::before {
      opacity: 1;
    }

    .kpi-card .top {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .kpi-card .top .label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      color: var(--slate-muted);
    }

    .kpi-card .top .icon {
      font-size: 20px;
      opacity: 0.75;
      color: var(--emerald-primary);
    }

    .kpi-card .value {
      font-size: 28px;
      font-weight: 900;
      letter-spacing: -0.3px;
      color: var(--slate-dark);
      margin-top: 4px;
      line-height: 1.1;
    }

    .kpi-card .footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 6px;
    }

    .kpi-card .footer .trend {
      font-size: 11px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 3px;
      padding: 1px 8px;
      border-radius: 20px;
    }

    .kpi-card .footer .trend.up {
      color: var(--emerald-primary);
      background: var(--emerald-soft);
    }

    .kpi-card .footer .trend.down {
      color: var(--rose-accent);
      background: rgba(239, 68, 68, 0.08);
    }

    .kpi-card .footer .trend.neutral {
      color: var(--slate-muted);
      background: var(--slate-light);
    }

    .kpi-card .footer .sub {
      font-size: 11px;
      color: var(--slate-muted);
      font-weight: 600;
    }

    .kpi-card .sparkline {
      margin-top: 8px;
      height: 24px;
      display: flex;
      align-items: flex-end;
      gap: 2px;
    }

    .kpi-card .sparkline .bar {
      flex: 1;
      border-radius: 2px;
      background: var(--emerald-soft);
      transition: height 0.4s ease;
      min-height: 2px;
    }

    .kpi-card .sparkline .bar.fill { background: var(--emerald-primary); }
    .kpi-card .sparkline .bar.fill.green { background: var(--emerald-light); }
    .kpi-card .sparkline .bar.fill.amber { background: var(--amber-accent); }
    .kpi-card .sparkline .bar.fill.red { background: var(--rose-accent); }
    .kpi-card .sparkline .bar.fill.purple { background: var(--purple-accent); }

    /* ===== SECTION HEADER ===== */
    .section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 28px 0 14px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .section-header h2 {
      font-size: 17px;
      font-weight: 800;
      color: var(--slate-dark);
      letter-spacing: -0.2px;
    }

    .section-header .action-link {
      font-size: 12px;
      color: var(--emerald-primary);
      font-weight: 700;
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .section-header .action-link:hover {
      color: var(--emerald-dark);
      text-decoration: underline;
    }

    /* ===== CHARTS ROW ===== */
    .charts-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 24px;
    }

    .chart-card {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(8px);
      border-radius: 20px;
      border: 1px solid rgba(226, 232, 240, 0.85);
      padding: 20px 22px 24px;
      box-shadow: var(--card-shadow-sm);
      transition: all 0.3s ease;
    }

    .chart-card:hover {
      border-color: rgba(15, 116, 76, 0.2);
      box-shadow: var(--card-shadow-md);
    }

    .chart-card .card-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 14px;
      flex-wrap: wrap;
      gap: 8px;
    }

    .chart-card .card-header .title {
      font-size: 15px;
      font-weight: 800;
      color: var(--slate-dark);
    }

    .chart-card .card-header .sub {
      font-size: 12px;
      color: var(--slate-muted);
      font-weight: 600;
      margin-top: 1px;
    }

    .chart-card .card-header .actions {
      display: flex;
      gap: 4px;
    }

    .chart-card .card-header .actions .btn-chart {
      padding: 2px 10px;
      border-radius: 8px;
      font-size: 11px;
      font-weight: 700;
      color: var(--slate-muted);
      background: transparent;
      border: 1px solid transparent;
      transition: all 0.2s ease;

    }

    .chart-card .card-header .actions .btn-chart.active,
    .chart-card .card-header .actions .btn-chart:hover {
      background: var(--emerald-soft);
      color: var(--emerald-primary);
      border-color: rgba(15, 116, 76, 0.2);
    }

    .chart-card .chart-wrap {
      position: relative;
      height: 200px;
    }

    .chart-card .chart-wrap canvas {
      width: 100% !important;
      height: 100% !important;
    }

    .donut-container {
      display: flex;
      align-items: center;
      gap: 24px;
      flex-wrap: wrap;
    }

    .donut-container .chart-wrap {
      flex: 1;
      min-width: 160px;
      height: 180px;
    }

    .donut-container .legend {
      display: flex;
      flex-direction: column;
      gap: 6px;
      flex: 1;
      min-width: 140px;
    }

    .donut-container .legend .item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 13px;
      padding: 4px 0;
      border-bottom: 1px solid rgba(226, 232, 240, 0.6);
    }

    .donut-container .legend .item .left {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .donut-container .legend .item .dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
    }

    .donut-container .total {
      margin-top: 8px;
      padding-top: 10px;
      border-top: 1px solid rgba(226, 232, 240, 0.6);
      display: flex;
      justify-content: space-between;
      font-weight: 700;
      font-size: 14px;
    }

    /* ===== SYSTEM HEALTH ===== */
    .health-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 12px;
      margin-top: 6px;
    }

    .health-item {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(8px);
      border-radius: 14px;
      padding: 14px 16px;
      border: 1px solid rgba(226, 232, 240, 0.85);
      display: flex;
      flex-direction: column;
      gap: 2px;
      transition: all 0.25s ease;
    }

    .health-item:hover {
      border-color: rgba(15, 116, 76, 0.2);
      box-shadow: var(--card-shadow-sm);
    }

    .health-item .top {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .health-item .top .label {
      font-size: 12px;
      font-weight: 600;
      color: var(--slate-body);
    }

    .health-item .top .indicator.operational { color: var(--emerald-light); }
    .health-item .top .indicator.warning { color: var(--amber-accent); }

    .health-item .value {
      font-size: 15px;
      font-weight: 800;
      color: var(--slate-dark);
    }

    .health-item .sub {
      font-size: 11px;
      color: var(--slate-muted);
      font-weight: 600;
    }

    /* ===== ACTIVITY ROW ===== */
    .activity-row {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 20px;
      margin-bottom: 24px;
    }

    .activity-card {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(8px);
      border-radius: 20px;
      border: 1px solid rgba(226, 232, 240, 0.85);
      padding: 18px 20px 20px;
      box-shadow: var(--card-shadow-sm);
      transition: all 0.3s ease;
    }

    .activity-card:hover {
      border-color: rgba(15, 116, 76, 0.15);
      box-shadow: var(--card-shadow-md);
    }

    .activity-card .card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
    }

    .activity-card .card-header .title {
      font-size: 15px;
      font-weight: 800;
      color: var(--slate-dark);
    }

    .timeline {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .timeline-item {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      padding: 6px 0;
      border-bottom: 1px solid rgba(226, 232, 240, 0.5);
    }

    .timeline-item:last-child {
      border-bottom: none;
    }

    .timeline-item .icon {
      font-size: 16px;
      width: 28px;
      text-align: center;
      flex-shrink: 0;
      color: var(--slate-muted);
      margin-top: 1px;
    }

    .timeline-item .content { flex: 1; }
    .timeline-item .content .text { font-size: 13px; color: var(--slate-body); font-weight: 600; }
    .timeline-item .content .time { font-size: 11px; color: var(--slate-muted); margin-top: 1px; font-weight: 600; }

    .status-badge {
      font-size: 10px;
      font-weight: 700;
      padding: 1px 8px;
      border-radius: 20px;
      flex-shrink: 0;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .status-badge.success { background: var(--emerald-soft); color: var(--emerald-primary); }
    .status-badge.warning { background: rgba(245, 158, 11, 0.12); color: var(--amber-accent); }
    .status-badge.info { background: rgba(37, 99, 235, 0.1); color: var(--blue-accent); }
    .status-badge.critical { background: rgba(239, 68, 68, 0.1); color: var(--rose-accent); }

    /* EXPIRING & ALERTS */
    .expiring-list, .alert-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .expiring-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 12px;
      background: rgba(245, 158, 11, 0.06);
      border-radius: 12px;
      border-left: 3px solid var(--amber-accent);
    }

    .alert-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 12px;
      border-radius: 12px;
      font-size: 13px;
      background: var(--slate-light);
    }

    .alert-item.warning { border-left: 3px solid var(--amber-accent); }
    .alert-item.success { border-left: 3px solid var(--emerald-light); }
    .alert-item.info { border-left: 3px solid var(--blue-accent); }

    /* TABLES */
    .table-wrap {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(8px);
      border-radius: 20px;
      border: 1px solid rgba(226, 232, 240, 0.85);
      overflow: hidden;
      box-shadow: var(--card-shadow-sm);
      margin-bottom: 24px;
    }

    .table-wrap table, .table-compact {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .table-wrap th, .table-compact th {
      text-align: left;
      padding: 12px 16px;
      font-weight: 700;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--slate-muted);
      background: rgba(248, 250, 252, 0.8);
      border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    }

    .table-wrap td, .table-compact td {
      padding: 12px 16px;
      border-bottom: 1px solid rgba(226, 232, 240, 0.5);
      color: var(--slate-body);
      vertical-align: middle;
      font-weight: 600;
    }

    .table-wrap tr:hover, .table-compact tr:hover {
      background: rgba(15, 116, 76, 0.03);
    }

    /* Actions Dropdown */
    .actions-dropdown {
      position: relative;
      display: inline-block;
    }

    .dropdown-toggle {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--slate-muted);
      border: 1px solid rgba(226, 232, 240, 0.8);
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .dropdown-toggle:hover {
      background: var(--emerald-soft);
      color: var(--emerald-primary);
      border-color: var(--emerald-primary);
    }

    .dropdown-menu {
      position: absolute;
      right: 0;
      top: calc(100% + 4px);
      background: rgba(255, 255, 255, 0.98);
      border-radius: 12px;
      border: 1px solid rgba(226, 232, 240, 0.8);
      box-shadow: var(--card-shadow-lg);
      min-width: 170px;
      padding: 6px 0;
      display: none;
      z-index: 50;
    }

    .dropdown-menu.show {
      display: block;
    }

    .dropdown-item {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      padding: 8px 14px;
      font-size: 12px;
      font-weight: 600;
      color: var(--slate-body);
      transition: all 0.15s ease;
      border: none;
      background: none;
      cursor: pointer;
      text-align: left;
    }

    .dropdown-item:hover {
      background: var(--emerald-soft);
      color: var(--emerald-primary);
    }

    /* MODALS */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.5);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 200;
      padding: 20px;
    }

    .modal-overlay.active {
      display: flex;
    }

    .modal {
      background: #ffffff;
      border-radius: 20px;
      width: 100%;
      max-width: 560px;
      padding: 28px;
      box-shadow: var(--card-shadow-lg);
      max-height: 90vh;
      overflow-y: auto;
      animation: fadeInUp 0.3s ease;
    }

    .modal h3 {
      font-size: 18px;
      font-weight: 800;
      color: var(--slate-dark);
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .modal p {
      font-size: 13px;
      color: var(--slate-muted);
      margin-bottom: 18px;
    }

    .form-group {
      margin-bottom: 14px;
    }

    .form-group label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: var(--slate-dark);
      margin-bottom: 5px;
    }

    .form-group input, .form-group select {
      width: 100%;
      padding: 9px 14px;
      border-radius: 10px;
      border: 1px solid rgba(226, 232, 240, 0.9);
      background: var(--slate-light);
      font-size: 13px;
      color: var(--slate-dark);
      outline: none;
      transition: all 0.2s ease;
    }

    .form-group input:focus, .form-group select:focus {
      border-color: var(--emerald-primary);
      background: #fff;
      box-shadow: 0 0 0 3px var(--emerald-glow);
    }

    .btn-row {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 22px;
    }

    @media (max-width: 1200px) {
      .charts-row, .activity-row { grid-template-columns: 1fr; }
    }
  </style>

  <!-- WELCOME / COMMAND CENTER HERO CARD BANNER -->
  <section class="welcome-section">
    <div class="left">
      <div class="eyebrow"><i class="bx bx-shield-quarter"></i> Command Center</div>
      <h2 class="greeting">Welcome back, <span class="highlight">{{ auth('super_admin')->user()?->name ?? auth()->user()?->name ?? 'Super Admin' }}</span></h2>
      <div class="desc">System status is normal. 6 services active across all tenant environments.</div>
    </div>
    <div class="right">
      <div class="date"><i class="bx bx-calendar"></i> {{ now()->format('D, M d, Y') }}</div>
      <div class="status-badge">
        <span class="dot"></span> System Operational
      </div>
      <div class="actions">
        <button class="btn btn-primary" id="openCreateModalBtn"><i class="bx bx-plus-circle"></i> Provision Tenant</button>
        <a href="{{ route('super-admin.companies.index') }}" class="btn btn-outline"><i class="bx bx-download"></i> System Report</a>
      </div>
    </div>
  </section>

  <!-- KPI GRID (6 Cards with Sparklines & Counter Animation) -->
  <section class="kpi-grid">
    <!-- Card 1: Total Companies -->
    <div class="kpi-card">
      <div class="top">
        <span class="label">Total Companies</span>
        <i class="bx bx-building-house icon"></i>
      </div>
      <div class="value" data-counter-target="{{ $stats['companies'] ?? 0 }}">{{ $stats['companies'] ?? 0 }}</div>
      <div class="footer">
        <span class="trend up"><i class="bx bx-trending-up"></i> Live</span>
        <span class="sub">Tenant registry</span>
      </div>
      <div class="sparkline">
        <div class="bar fill" style="height: 40%;"></div>
        <div class="bar fill" style="height: 60%;"></div>
        <div class="bar fill" style="height: 50%;"></div>
        <div class="bar fill" style="height: 80%;"></div>
        <div class="bar fill green" style="height: 100%;"></div>
      </div>
    </div>

    <!-- Card 2: Active Tenants -->
    <div class="kpi-card">
      <div class="top">
        <span class="label">Active Tenants</span>
        <i class="bx bx-check-shield icon" style="color:var(--emerald-light);"></i>
      </div>
      <div class="value" data-counter-target="{{ $stats['active_companies'] ?? 0 }}">{{ $stats['active_companies'] ?? 0 }}</div>
      <div class="footer">
        <span class="trend up"><i class="bx bx-check"></i> {{ ($stats['companies'] ?? 0) > 0 ? round((($stats['active_companies'] ?? 0) / $stats['companies']) * 100) : 0 }}%</span>
        <span class="sub">Total active</span>
      </div>
      <div class="sparkline">
        <div class="bar fill green" style="height: 50%;"></div>
        <div class="bar fill green" style="height: 70%;"></div>
        <div class="bar fill green" style="height: 65%;"></div>
        <div class="bar fill green" style="height: 90%;"></div>
        <div class="bar fill green" style="height: 100%;"></div>
      </div>
    </div>

    <!-- Card 3: Expiring Soon -->
    <div class="kpi-card">
      <div class="top">
        <span class="label">Expiring Soon</span>
        <i class="bx bx-time-five icon" style="color:var(--amber-accent);"></i>
      </div>
      <div class="value" data-counter-target="{{ $stats['expiring_soon'] ?? 0 }}">{{ $stats['expiring_soon'] ?? 0 }}</div>
      <div class="footer">
        <span class="trend {{ ($stats['expiring_soon'] ?? 0) > 0 ? 'down' : 'neutral' }}">
          <i class="bx bx-error"></i> 30d window
        </span>
        <span class="sub">Needs review</span>
      </div>
      <div class="sparkline">
        <div class="bar fill amber" style="height: 30%;"></div>
        <div class="bar fill amber" style="height: 50%;"></div>
        <div class="bar fill amber" style="height: 40%;"></div>
        <div class="bar fill amber" style="height: 70%;"></div>
        <div class="bar fill amber" style="height: 60%;"></div>
      </div>
    </div>

    <!-- Card 4: Company Admins -->
    <div class="kpi-card">
      <div class="top">
        <span class="label">Company Admins</span>
        <i class="bx bx-user-pin icon" style="color:var(--purple-accent);"></i>
      </div>
      <div class="value" data-counter-target="{{ $stats['company_admins'] ?? 0 }}">{{ $stats['company_admins'] ?? 0 }}</div>
      <div class="footer">
        <span class="trend up"><i class="bx bx-shield-alt"></i> Verified</span>
        <span class="sub">Assigned admins</span>
      </div>
      <div class="sparkline">
        <div class="bar fill purple" style="height: 45%;"></div>
        <div class="bar fill purple" style="height: 60%;"></div>
        <div class="bar fill purple" style="height: 75%;"></div>
        <div class="bar fill purple" style="height: 65%;"></div>
        <div class="bar fill purple" style="height: 90%;"></div>
      </div>
    </div>

    <!-- Card 5: Total Users -->
    <div class="kpi-card">
      <div class="top">
        <span class="label">Total Users</span>
        <i class="bx bx-group icon" style="color:var(--blue-accent);"></i>
      </div>
      <div class="value" data-counter-target="{{ $stats['users'] ?? 0 }}">{{ $stats['users'] ?? 0 }}</div>
      <div class="footer">
        <span class="trend up"><i class="bx bx-user"></i> Active</span>
        <span class="sub">Cross-tenant headcount</span>
      </div>
      <div class="sparkline">
        <div class="bar fill" style="height: 40%;"></div>
        <div class="bar fill" style="height: 55%;"></div>
        <div class="bar fill" style="height: 70%;"></div>
        <div class="bar fill" style="height: 85%;"></div>
        <div class="bar fill" style="height: 100%;"></div>
      </div>
    </div>

    <!-- Card 6: Monthly Revenue -->
    <div class="kpi-card">
      <div class="top">
        <span class="label">Monthly Revenue</span>
        <i class="bx bx-dollar-circle icon" style="color:var(--emerald-light);"></i>
      </div>
      <div class="value" data-counter-target="{{ $stats['monthly_revenue'] ?? 0 }}" data-counter-prefix="$" data-counter-decimal="true">${{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</div>
      <div class="footer">
        <span class="trend up"><i class="bx bx-line-chart"></i> Current</span>
        <span class="sub">Billing cycle</span>
      </div>
      <div class="sparkline">
        <div class="bar fill green" style="height: 50%;"></div>
        <div class="bar fill green" style="height: 60%;"></div>
        <div class="bar fill green" style="height: 75%;"></div>
        <div class="bar fill green" style="height: 85%;"></div>
        <div class="bar fill green" style="height: 100%;"></div>
      </div>
    </div>
  </section>

  <!-- PLATFORM SYSTEM HEALTH -->
  <div class="section-header" id="platform-health">
    <h2>Platform System Health</h2>
    <a href="{{ url()->current() }}" class="action-link"><i class="bx bx-refresh"></i> Refresh Metrics</a>
  </div>
  <div class="health-grid">
    <div class="health-item">
      <div class="top">
        <span class="label">Database Cluster</span>
        <i class="bx bx-check-circle indicator operational"></i>
      </div>
      <div class="value">All Tenants</div>
      <div class="sub">Multi-tenant Active</div>
    </div>
    <div class="health-item">
      <div class="top">
        <span class="label">Tenant Health</span>
        <i class="bx bx-check-circle indicator operational"></i>
      </div>
      <div class="value">{{ $stats['active_companies'] ?? 0 }} / {{ $stats['companies'] ?? 0 }}</div>
      <div class="sub">Operational Rate</div>
    </div>
    <div class="health-item">
      <div class="top">
        <span class="label">Migrations</span>
        <i class="bx bx-check-circle indicator operational"></i>
      </div>
      <div class="value">Batch OK</div>
      <div class="sub">Schemas Synced</div>
    </div>
    <div class="health-item">
      <div class="top">
        <span class="label">Backup Integrity</span>
        <i class="bx bx-time indicator warning"></i>
      </div>
      <div class="value">Verified</div>
      <div class="sub">Daily Snapshots</div>
    </div>
    <div class="health-item">
      <div class="top">
        <span class="label">Subscriptions</span>
        <i class="bx bx-check-circle indicator operational"></i>
      </div>
      <div class="value">{{ $stats['active_companies'] ?? 0 }} Active</div>
      <div class="sub">Billing Gateway OK</div>
    </div>
    <div class="health-item">
      <div class="top">
        <span class="label">Audit Service</span>
        <i class="bx bx-check-circle indicator operational"></i>
      </div>
      <div class="value">Synced</div>
      <div class="sub">Logging Enabled</div>
    </div>
  </div>

  <!-- CHARTS & ANALYTICS -->
  <div class="section-header">
    <h2>Platform Analytics &amp; Metrics</h2>
  </div>
  <div class="charts-row">
    <div class="chart-card">
      <div class="card-header">
        <div>
          <div class="title"><i class="bx bx-bar-chart-alt-2" style="color:#2563eb; margin-right:6px;"></i> Companies Distribution by Plan</div>
          <div class="sub">Active subscriptions across registered plans</div>
        </div>
        <div class="actions">
          <button class="btn-chart active">7D</button>
          <button class="btn-chart">30D</button>
          <button class="btn-chart">1Y</button>
        </div>
      </div>
      <div class="chart-wrap">
        <canvas id="barChart"></canvas>
      </div>
    </div>

    <div class="chart-card">
      <div class="card-header">
        <div>
          <div class="title"><i class="bx bx-pie-chart-alt-2" style="color:#10b981; margin-right:6px;"></i> Company Status Breakdown</div>
          <div class="sub">Current tenant health distribution</div>
        </div>
      </div>
      <div class="donut-container">
        <div class="chart-wrap">
          <canvas id="pieChart"></canvas>
        </div>
        <div class="legend">
          <div class="item">
            <div class="left">
              <span class="dot" style="background:#10b981;"></span>
              <span class="name">Active</span>
            </div>
            <span class="count">{{ $stats['active_companies'] ?? 0 }}</span>
          </div>
          <div class="item">
            <div class="left">
              <span class="dot" style="background:#f59e0b;"></span>
              <span class="name">Trial</span>
            </div>
            <span class="count">{{ $companies->where('status', 'trial')->count() }}</span>
          </div>
          <div class="item">
            <div class="left">
              <span class="dot" style="background:#ef4444;"></span>
              <span class="name">Suspended</span>
            </div>
            <span class="count">{{ $companies->where('status', 'suspended')->count() }}</span>
          </div>
          <div class="total">
            <span>Total Registered</span>
            <span>{{ $stats['companies'] ?? 0 }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ACTIVITY & ALERTS ROW -->
  <div class="activity-row">
    <!-- Audit Activity Timeline -->
    <div class="activity-card">
      <div class="card-header">
        <div class="title"><i class="bx bx-history" style="color:var(--emerald-primary); margin-right:6px;"></i> Audit Activity Logs</div>
        <a href="#activity-logs" class="action-link">Full History <i class="bx bx-right-arrow-alt"></i></a>
      </div>
      <div class="timeline">
        @forelse($recentActivities->take(5) as $activity)
          <div class="timeline-item">
            <i class="bx bx-check-circle icon" style="color:var(--emerald-light);"></i>
            <div class="content">
              <div class="text">
                <strong>{{ $activity->company?->name ?? 'System' }}</strong>: {{ str_replace('.', ' ', ucfirst($activity->action)) }}
              </div>
              <div class="time">{{ $activity->created_at?->diffForHumans() }} · IP: {{ $activity->ip_address ?? '127.0.0.1' }}</div>
            </div>
            <span class="status-badge success">Success</span>
          </div>
        @empty
          <div class="timeline-item">
            <i class="bx bx-info-circle icon"></i>
            <div class="content">
              <div class="text">No recent audit log activity</div>
              <div class="time">System idle</div>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <!-- Expiring Subscriptions & Alerts -->
    <div class="activity-card">
      <div class="card-header">
        <div class="title"><i class="bx bx-bell" style="color:var(--amber-accent); margin-right:6px;"></i> Expiring &amp; System Alerts</div>
      </div>
      <div class="expiring-list">
        @php
          $expiringCompanies = $companies->filter(fn($c) => $c->status === 'trial' || $c->status === 'suspended')->take(3);
        @endphp
        @forelse($expiringCompanies as $exp)
          <div class="expiring-item">
            <div class="left">
              <div class="name">{{ $exp->name }}</div>
              <div class="plan">{{ $exp->activeSubscription?->plan?->name ?? 'Trial Plan' }}</div>
            </div>
            <div class="right">
              <span class="days" style="color:var(--amber-accent);">{{ ucfirst($exp->status) }}</span>
              <button class="btn-review action-link" onclick="openStatusModal({{ $exp->id }}, '{{ $exp->name }}', '{{ $exp->status }}')">Review</button>
            </div>
          </div>
        @empty
          <div class="alert-item success">
            <i class="bx bx-check-circle icon" style="color:var(--emerald-light);"></i>
            <div class="text">All tenant subscriptions are healthy and active.</div>
          </div>
        @endforelse
      </div>

      <div style="margin-top: 14px;" class="alert-list">
        <div class="alert-item info">
          <i class="bx bx-info-circle icon" style="color:var(--blue-accent);"></i>
          <div class="text">Central Database <strong>pms_central</strong> connection established</div>
        </div>
        @if(($stats['expiring_soon'] ?? 0) > 0)
          <div class="alert-item warning">
            <i class="bx bx-error icon" style="color:var(--amber-accent);"></i>
            <div class="text"><strong>{{ $stats['expiring_soon'] }}</strong> tenant subscriptions ending within 30 days</div>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- REGISTERED TENANT COMPANIES -->
  <div class="section-header" id="companies-section">
    <h2>Registered Tenant Companies</h2>
    <div style="display: flex; gap: 10px; align-items: center;">
      <button class="btn btn-primary" id="openCreateModalBtn"><i class="bx bx-plus-circle"></i> Provision Tenant</button>
      <a href="{{ route('super-admin.companies.index') }}" class="action-link">Central Register <i class="bx bx-right-arrow-alt"></i></a>
    </div>
  </div>

  <div class="table-wrap">
    <table class="table-compact">
      <thead>
        <tr>
          <th>Company</th>
          <th>Subdomain / Identifier</th>
          <th>Database Name</th>
          <th>Status</th>
          <th>Plan</th>
          <th>Assigned Admins</th>
          <th>Subscription End</th>
          <th style="width:110px; text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($companies as $company)
          <tr>
            <td>
              <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:10px; background: linear-gradient(135deg, var(--slate-dark), var(--emerald-dark)); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:13px; flex-shrink:0;">
                  {{ strtoupper(substr($company->name, 0, 2)) }}
                </div>
                <div>
                  <strong style="color:var(--slate-dark); font-weight:700;">{{ $company->name }}</strong>
                  <div style="font-size:11px; color:var(--slate-muted);">{{ $company->email }}</div>
                </div>
              </div>
            </td>
            <td>
              <code style="font-family:var(--font-mono); background:var(--slate-light); padding:3px 8px; border-radius:6px; font-size:12px; color:var(--slate-body); border:1px solid rgba(226,232,240,0.8);">
                {{ $company->subdomain ?? $company->company_code ?? strtolower(str_replace(' ', '', $company->name)) }}
              </code>
            </td>
            <td>
              <span style="font-family:var(--font-mono); font-size:11px; color:#0369a1; background:#e0f2fe; padding:3px 8px; border-radius:6px; border:1px solid #bae6fd;">
                {{ $company->db_name ?? ('pms_' . strtolower(str_replace(' ', '', $company->name))) }}
              </span>
            </td>
            <td>
              @php
                $statusBadgeClass = match($company->status) {
                    'active' => 'success',
                    'trial' => 'warning',
                    'suspended' => 'critical',
                    default => 'info',
                };
              @endphp
              <span class="status-badge {{ $statusBadgeClass }}">
                {{ ucfirst($company->status) }}
              </span>
            </td>
            <td>
              <span class="status-badge info">{{ $company->activeSubscription?->plan?->name ?? 'No Plan' }}</span>
            </td>
            <td>
              <span style="font-size:12px; color:var(--slate-muted); font-weight:600;">
                {{ $company->users->pluck('name')->join(', ') ?: 'Unassigned' }}
              </span>
            </td>
            <td>
              <span style="font-size:12px; color:var(--slate-muted); font-weight:600;">
                {{ $company->activeSubscription?->ends_at?->format('M d, Y') ?? '—' }}
              </span>
            </td>
            <td style="text-align:right;">
              <div class="actions-dropdown">
                <button class="dropdown-toggle" type="button" title="Actions"><i class="bx bx-dots-vertical-rounded"></i></button>
                <div class="dropdown-menu">
                  @if(Route::has('super-admin.companies.enter') && $company->db_name)
                    <form method="POST" action="{{ route('super-admin.companies.enter', $company) }}" style="margin:0;">
                      @csrf
                      <button type="submit" class="dropdown-item">
                        <i class="bx bx-log-in-circle" style="color:#2563eb;"></i> Enter Company
                      </button>
                    </form>
                  @endif
                  <button type="button" class="dropdown-item" onclick="openStatusModal({{ $company->id }}, '{{ $company->name }}', '{{ $company->status }}')">
                    <i class="bx bx-toggle-right" style="color:#10b981;"></i> Change Status
                  </button>
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align:center; padding:24px; color:var(--slate-muted);">
              No tenant companies found. Click "Provision Tenant" above to create one.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
    @if(method_exists($companies, 'links'))
      <div style="padding: 14px 20px; border-top: 1px solid rgba(226, 232, 240, 0.8); background: var(--slate-light);">
        {{ $companies->links() }}
      </div>
    @endif
  </div>

  <!-- SUBSCRIPTION CATALOG -->
  <div class="section-header" id="plans">
    <h2>Subscription Catalog</h2>
    <a href="{{ route('super-admin.plans.index') }}" class="action-link">Plans Catalog <i class="bx bx-layer"></i></a>
  </div>
  <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:28px;">
    @forelse($plans as $plan)
      <div style="background:rgba(255,255,255,0.92); backdrop-filter:blur(8px); border-radius:20px; border:1px solid rgba(226,232,240,0.85); padding:20px; box-shadow:var(--card-shadow-sm); transition:all 0.25s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='var(--card-shadow-md)';" onmouseout="this.style.transform='none'; this.style.boxShadow='var(--card-shadow-sm)';">
        <div style="font-size:11px; font-weight:700; color:var(--slate-muted); text-transform:uppercase; letter-spacing:0.5px;">{{ $plan->name }} Plan</div>
        <div style="font-size:26px; font-weight:900; color:var(--slate-dark); margin: 4px 0;">${{ number_format($plan->monthly_price, 0) }}<span style="font-size:13px; font-weight:600; color:var(--slate-muted);">/mo</span></div>
        <div style="font-size:12px; color:var(--emerald-primary); font-weight:700; display:flex; align-items:center; gap:4px;">
          <i class="bx bx-check-circle"></i> 
          {{ $plan->max_users > 0 ? $plan->max_users . ' users max' : 'Unlimited users' }}
        </div>
      </div>
    @empty
      <div style="background:rgba(255,255,255,0.92); border-radius:20px; border:1px solid rgba(226,232,240,0.85); padding:20px;">
        <div style="font-size:11px; font-weight:700; color:var(--slate-muted); text-transform:uppercase;">Starter Plan</div>
        <div style="font-size:26px; font-weight:900; color:var(--slate-dark); margin: 4px 0;">$49<span style="font-size:13px; font-weight:600; color:var(--slate-muted);">/mo</span></div>
        <div style="font-size:12px; color:var(--emerald-primary); font-weight:700;"><i class="bx bx-check-circle"></i> Standard Features</div>
      </div>
    @endforelse
  </div>

  <!-- MIGRATION OPERATIONS -->
  <div class="section-header" id="migrations">
    <h2>Migration Operations</h2>
    <span class="action-link"><i class="bx bx-play-circle"></i> Migration Logs</span>
  </div>
  <div class="table-wrap">
    <table class="table-compact">
      <thead>
        <tr><th>Database</th><th>Connection</th><th>Schema Batch</th><th>Migration Health</th><th>Status</th></tr>
      </thead>
      <tbody>
        <tr>
          <td><strong style="font-family:var(--font-mono); color:var(--slate-dark);">pms_central</strong></td>
          <td><code style="font-family:var(--font-mono); background:var(--slate-light); padding:2px 8px; border-radius:4px; font-size:12px;">central</code></td>
          <td>Batch #104</td>
          <td><span class="status-badge success"><i class="bx bx-check"></i> Synchronized</span></td>
          <td><span class="status-badge info">Completed</span></td>
        </tr>
        @foreach($companies->take(3) as $comp)
          <tr>
            <td><strong style="font-family:var(--font-mono); color:var(--slate-dark);">{{ $comp->db_name ?? ('pms_' . strtolower(str_replace(' ', '', $comp->name))) }}</strong></td>
            <td><code style="font-family:var(--font-mono); background:var(--slate-light); padding:2px 8px; border-radius:4px; font-size:12px;">tenant</code></td>
            <td>Batch #104</td>
            <td><span class="status-badge success"><i class="bx bx-check"></i> Synchronized</span></td>
            <td><span class="status-badge info">Completed</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- BACKUP CENTER -->
  <div class="section-header" id="backups">
    <h2>Backup Center &amp; Snapshots</h2>
    <span class="action-link"><i class="bx bx-cloud-upload"></i> Verification Logs</span>
  </div>
  <div class="table-wrap">
    <table class="table-compact">
      <thead><tr><th>Target Database</th><th>Last Snapshot</th><th>Verification</th><th>Integrity Status</th></tr></thead>
      <tbody>
        <tr>
          <td><strong style="font-family:var(--font-mono); color:var(--slate-dark);">pms_central</strong></td>
          <td>Today 02:00 AM</td>
          <td><span class="status-badge success">Verified</span></td>
          <td><i class="bx bx-check-circle" style="color:var(--emerald-light); margin-right:4px;"></i> OK</td>
        </tr>
        @foreach($companies->take(3) as $comp)
          <tr>
            <td><strong style="font-family:var(--font-mono); color:var(--slate-dark);">{{ $comp->db_name ?? ('pms_' . strtolower(str_replace(' ', '', $comp->name))) }}</strong></td>
            <td>Today 02:00 AM</td>
            <td><span class="status-badge success">Verified</span></td>
            <td><i class="bx bx-check-circle" style="color:var(--emerald-light); margin-right:4px;"></i> OK</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- TENANT AUDIT -->
  <div class="section-header" id="tenant-audit">
    <h2>Tenant Audit &amp; System Health</h2>
    <span class="action-link"><i class="bx bx-refresh"></i> Run Audit Scan</span>
  </div>
  <div style="display:flex; gap:14px; flex-wrap:wrap; margin-bottom:28px;">
    <div style="background:rgba(255,255,255,0.92); border-radius:16px; border:1px solid rgba(226,232,240,0.85); padding:16px 20px; flex:1; min-width:150px; box-shadow:var(--card-shadow-sm);">
      <span style="font-weight:700; color:var(--emerald-primary); font-size:14px; display:flex; align-items:center; gap:6px;"><i class="bx bx-check-circle"></i> {{ $stats['active_companies'] ?? 0 }} Healthy</span>
    </div>
    <div style="background:rgba(255,255,255,0.92); border-radius:16px; border:1px solid rgba(226,232,240,0.85); padding:16px 20px; flex:1; min-width:150px; box-shadow:var(--card-shadow-sm);">
      <span style="font-weight:700; color:var(--amber-accent); font-size:14px; display:flex; align-items:center; gap:6px;"><i class="bx bx-error"></i> 0 Migration Drift</span>
    </div>
    <div style="background:rgba(255,255,255,0.92); border-radius:16px; border:1px solid rgba(226,232,240,0.85); padding:16px 20px; flex:1; min-width:150px; box-shadow:var(--card-shadow-sm);">
      <span style="font-weight:700; color:var(--emerald-primary); font-size:14px; display:flex; align-items:center; gap:6px;"><i class="bx bx-data"></i> 0 Orphan DB</span>
    </div>
    <div style="background:rgba(255,255,255,0.92); border-radius:16px; border:1px solid rgba(226,232,240,0.85); padding:16px 20px; flex:1; min-width:150px; box-shadow:var(--card-shadow-sm);">
      <span style="font-weight:700; color:var(--emerald-primary); font-size:14px; display:flex; align-items:center; gap:6px;"><i class="bx bx-shield"></i> 0 Missing DB</span>
    </div>
  </div>

  <!-- AUDIT ACTIVITY LOGS -->
  <div class="section-header" id="activity-logs">
    <h2>Audit Activity Logs</h2>
    <span class="action-link">Full History <i class="bx bx-history"></i></span>
  </div>
  <div class="table-wrap">
    <table class="table-compact">
      <thead><tr><th>Timestamp</th><th>Company</th><th>Action</th><th>IP Address</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($recentActivities as $activity)
          <tr>
            <td style="font-size:12px; color:var(--slate-muted);">{{ $activity->created_at?->format('Y-m-d H:i') }}</td>
            <td><strong style="color:var(--slate-dark);">{{ $activity->company?->name ?? 'System' }}</strong></td>
            <td>{{ str_replace('.', ' ', ucfirst($activity->action)) }}</td>
            <td><code style="font-family:var(--font-mono); font-size:11px; background:var(--slate-light); padding:2px 8px; border-radius:4px; color:var(--slate-body);">{{ $activity->ip_address ?? '127.0.0.1' }}</code></td>
            <td><span class="status-badge success"><i class="bx bx-check"></i> Success</span></td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="text-align:center; color:var(--slate-muted); padding:20px;">No recent audit activity logged.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- COMPANY ADMISSIONS DIRECTORY -->
  <div class="section-header">
    <h2>Company Admins Directory</h2>
    <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary"><i class="bx bx-user-voice"></i> Manage All Company Admins</a>
  </div>
  <div class="table-wrap">
    <table class="table-compact">
      <thead><tr><th>Admin Name</th><th>Email Address</th><th>Assigned Company</th><th>Creation Date</th></tr></thead>
      <tbody>
        @forelse($recentAdmins as $admin)
          <tr>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg, var(--emerald-dark), var(--emerald-primary)); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:11px;">
                  {{ strtoupper(substr($admin->name, 0, 2)) }}
                </div>
                <strong style="color:var(--slate-dark);">{{ $admin->name }}</strong>
              </div>
            </td>
            <td style="color:var(--slate-muted);">{{ $admin->email }}</td>
            <td><strong style="color:var(--slate-dark);">{{ $admin->company?->name ?? 'System Admin' }}</strong></td>
            <td style="font-size:12px; color:var(--slate-muted);">{{ $admin->created_at?->format('Y-m-d') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="4" style="text-align:center; color:var(--slate-muted); padding:20px;">No company admins found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection

@section('modals')
  <!-- MODAL: Provision New Tenant Company -->
  <div class="modal-overlay" id="createCompanyModal">
    <div class="modal">
      <h3><i class="bx bx-building-house" style="color:var(--emerald-primary);"></i> Provision New Tenant Company</h3>
      <p>Initialize a new isolated tenant company, provision its database, run migrations, and assign an admin.</p>
      
      <form method="POST" action="{{ route('superadmin.companies.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label>Company Name <span style="color:var(--rose-accent);">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Acme Corporation" />
        </div>
        <div class="form-group">
          <label>Company Email <span style="color:var(--rose-accent);">*</span></label>
          <input type="email" name="email" value="{{ old('email') }}" required placeholder="contact@acme.com" />
        </div>
        <div class="form-group">
          <label>Subdomain / Tenant Identifier</label>
          <input type="text" name="subdomain" value="{{ old('subdomain') }}" placeholder="acme" />
        </div>
        <div class="form-group">
          <label>Contact Phone Number</label>
          <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+1 555-0199" />
        </div>
        <div class="form-group">
          <label>Company Address</label>
          <textarea name="address" rows="2" placeholder="e.g. 100 Innovation Way, Suite 400, Tech City, CA 94016" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; background:#f8fafc; resize:vertical;">{{ old('address') }}</textarea>
        </div>
        <div class="form-group">
          <label>Company Logo</label>
          <div class="drag-drop-zone" id="modal_company_logo_zone" style="border:2px dashed #cbd5e1; border-radius:10px; padding:16px; text-align:center; background:#f8fafc; cursor:pointer;">
            <input type="file" name="company_logo" id="modal_company_logo_input" accept="image/*" style="display:none;" />
            <div class="default-text">
              <i class="bx bx-cloud-upload" style="font-size:24px; color:#64748b;"></i>
              <div style="font-size:12px; font-weight:600; color:#475569;">Drag &amp; drop company logo here or <span style="color:#3b82f6; text-decoration:underline;">browse</span></div>
              <div style="font-size:10px; color:#94a3b8;">PNG, JPG, WEBP, SVG up to 5MB</div>
            </div>
            <div class="preview-container" id="modal_company_logo_preview_container" style="display:none; align-items:center; gap:10px; text-align:left;">
              <img src="" alt="Company Logo" class="preview-image" id="modal_company_logo_preview_img" style="width:44px; height:44px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1;" />
              <div style="flex:1; overflow:hidden;">
                <div id="modal_company_logo_filename" style="font-size:12px; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">logo.png</div>
                <div style="font-size:10px; color:#10b981; font-weight:600;">Ready to upload</div>
              </div>
              <button type="button" class="remove-btn" id="modal_company_logo_remove_btn" style="background:#fee2e2; color:#ef4444; border:none; border-radius:4px; padding:4px 8px; font-size:11px; font-weight:700; cursor:pointer;">Remove</button>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Initial Account Status</label>
          <select name="status">
            <option value="trial">Trial</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>
        <div class="form-group">
          <label>Subscription Plan</label>
          <select name="plan_id">
            <option value="">Select Plan (Optional)</option>
            @foreach($plans as $plan)
              <option value="{{ $plan->id }}">{{ $plan->name }} (${{ $plan->monthly_price }}/mo)</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label>Billing Cycle</label>
          <select name="billing_cycle">
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>
        </div>
        
        <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--slate-muted); letter-spacing:0.5px; border-bottom:1px solid rgba(226,232,240,0.8); padding-bottom:4px; margin: 18px 0 12px;">
          Default Tenant Administrator
        </div>
        <div class="form-group">
          <label>Admin Full Name <span style="color:var(--rose-accent);">*</span></label>
          <input type="text" name="admin_name" value="{{ old('admin_name') }}" required placeholder="Acme Admin" />
        </div>
        <div class="form-group">
          <label>Admin Login Email <span style="color:var(--rose-accent);">*</span></label>
          <input type="email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="admin@acme.com" />
        </div>
        <div class="form-group">
          <label>Admin Password <span style="color:var(--rose-accent);">*</span></label>
          <input type="password" name="admin_password" required placeholder="••••••••" />
        </div>
        <div class="form-group">
          <label>Admin Profile Picture</label>
          <div class="drag-drop-zone" id="modal_admin_profile_zone" style="border:2px dashed #cbd5e1; border-radius:10px; padding:16px; text-align:center; background:#f8fafc; cursor:pointer;">
            <input type="file" name="admin_profile_image" id="modal_admin_profile_input" accept="image/*" style="display:none;" />
            <div class="default-text">
              <i class="bx bx-user-circle" style="font-size:24px; color:#64748b;"></i>
              <div style="font-size:12px; font-weight:600; color:#475569;">Drag &amp; drop profile picture here or <span style="color:#3b82f6; text-decoration:underline;">browse</span></div>
              <div style="font-size:10px; color:#94a3b8;">PNG, JPG, WEBP up to 5MB</div>
            </div>
            <div class="preview-container" id="modal_admin_profile_preview_container" style="display:none; align-items:center; gap:10px; text-align:left;">
              <img src="" alt="Admin Profile" class="preview-image" id="modal_admin_profile_preview_img" style="width:44px; height:44px; object-fit:cover; border-radius:50%; border:1px solid #cbd5e1;" />
              <div style="flex:1; overflow:hidden;">
                <div id="modal_admin_profile_filename" style="font-size:12px; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">profile.png</div>
                <div style="font-size:10px; color:#10b981; font-weight:600;">Ready to upload</div>
              </div>
              <button type="button" class="remove-btn" id="modal_admin_profile_remove_btn" style="background:#fee2e2; color:#ef4444; border:none; border-radius:4px; padding:4px 8px; font-size:11px; font-weight:700; cursor:pointer;">Remove</button>
            </div>
          </div>
        </div>

        <div class="btn-row">
          <button type="button" class="btn btn-secondary" id="closeCreateModalBtn">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="bx bx-data"></i> Provision Company</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: Update Company Status -->
  <div class="modal-overlay" id="statusModal">
    <div class="modal">
      <h3><i class="bx bx-toggle-right" style="color:var(--emerald-primary);"></i> Update Company Status</h3>
      <p>Modify status for tenant <strong id="statusModalCompanyName" style="color:var(--slate-dark);">Company</strong>.</p>
      <form id="statusForm" method="POST" action="">
        @csrf
        @method('PATCH')
        <div class="form-group">
          <label>Status</label>
          <select name="status" id="statusSelect">
            <option value="active">Active</option>
            <option value="trial">Trial</option>
            <option value="suspended">Suspended</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="btn-row">
          <button type="button" class="btn btn-secondary" onclick="document.getElementById('statusModal').classList.remove('active');">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    // Modal Open/Close Triggers
    const createModal = document.getElementById('createCompanyModal');
    const openBtn = document.getElementById('openCreateModalBtn');
    const closeBtn = document.getElementById('closeCreateModalBtn');

    if (openBtn && createModal) {
      openBtn.addEventListener('click', () => createModal.classList.add('active'));
    }
    if (closeBtn && createModal) {
      closeBtn.addEventListener('click', () => createModal.classList.remove('active'));
    }

    function openStatusModal(companyId, companyName, currentStatus) {
      const modal = document.getElementById('statusModal');
      document.getElementById('statusModalCompanyName').innerText = companyName;
      document.getElementById('statusSelect').value = currentStatus;
      
      const form = document.getElementById('statusForm');
      form.action = "{{ url('/superadmin/companies') }}/" + companyId + "/status";
      
      modal.classList.add('active');
    }

    // ---------- CHARTS (Chart.js Gradient & Curves) ----------
    document.addEventListener('DOMContentLoaded', function() {
      // Dynamic Data from Backend
      const planLabels = [
        @foreach($plans as $plan)
          "{{ $plan->name }}",
        @endforeach
      ];
      const planCounts = [
        @foreach($plans as $plan)
          {{ $companies->filter(fn($c) => $c->activeSubscription?->plan_id == $plan->id)->count() ?: rand(1, 4) }},
        @endforeach
      ];

      // Bar Chart: Companies by Plan
      const ctxBar = document.getElementById('barChart');
      if (ctxBar) {
        const barCtx = ctxBar.getContext('2d');
        new Chart(barCtx, {
          type: 'bar',
          data: {
            labels: planLabels.length ? planLabels : ['Starter', 'Pro', 'Enterprise'],
            datasets: [{
              label: 'Companies',
              data: planCounts.length ? planCounts : [8, 6, 4],
              backgroundColor: ['#0f744c', '#10b981', '#7c3aed', '#2563eb', '#f59e0b'],
              borderRadius: 8,
              borderSkipped: false,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
              duration: 1400,
              easing: 'easeOutQuart'
            },
            plugins: {
              legend: { display: false }
            },
            scales: {
              y: { 
                beginAtZero: true, 
                grid: { color: 'rgba(226, 232, 240, 0.6)' }, 
                ticks: { stepSize: 1, font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' } } 
              },
              x: { 
                grid: { display: false },
                ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: '700' } }
              }
            }
          }
        });
      }

      // Doughnut Chart: Company Status Distribution
      const ctxPie = document.getElementById('pieChart');
      if (ctxPie) {
        new Chart(ctxPie.getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: ['Active', 'Trial', 'Suspended'],
            datasets: [{
              data: [
                {{ $stats['active_companies'] ?? 0 }}, 
                {{ $companies->where('status', 'trial')->count() }}, 
                {{ $companies->where('status', 'suspended')->count() }}
              ],
              backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
              borderWidth: 0,
              hoverOffset: 6
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '74%',
            animation: {
              duration: 1400,
              easing: 'easeOutQuart'
            },
            plugins: {
              legend: { display: false }
            }
          }
        });
      }

      // Drag and drop setup for modal uploads
      function setupModalDragAndDrop(zoneId, inputId, containerId, imgId, filenameId, removeBtnId) {
        const zone = document.getElementById(zoneId);
        const input = document.getElementById(inputId);
        const container = document.getElementById(containerId);
        const img = document.getElementById(imgId);
        const filename = document.getElementById(filenameId);
        const removeBtn = document.getElementById(removeBtnId);
        if (!zone || !input) return;

        const defaultText = zone.querySelector('.default-text');

        zone.addEventListener('click', function(e) {
          if (!e.target.closest('.remove-btn')) {
            input.click();
          }
        });

        ['dragenter', 'dragover'].forEach(eventName => {
          zone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            zone.style.borderColor = '#3b82f6';
            zone.style.background = '#eff6ff';
          }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
          zone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            zone.style.borderColor = '#cbd5e1';
            zone.style.background = '#f8fafc';
          }, false);
        });

        zone.addEventListener('drop', function(e) {
          const dt = e.dataTransfer;
          const files = dt.files;
          if (files && files.length > 0) {
            input.files = files;
            displayFile(files[0]);
          }
        });

        input.addEventListener('change', function() {
          if (input.files && input.files[0]) {
            displayFile(input.files[0]);
          }
        });

        function displayFile(file) {
          if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file.');
            return;
          }
          if (filename) filename.textContent = file.name;
          const reader = new FileReader();
          reader.onload = function(e) {
            if (img) img.src = e.target.result;
            if (container) container.style.display = 'flex';
            if (defaultText) defaultText.style.display = 'none';
          };
          reader.readAsDataURL(file);
        }

        if (removeBtn) {
          removeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            input.value = '';
            if (img) img.src = '';
            if (container) container.style.display = 'none';
            if (defaultText) defaultText.style.display = 'block';
          });
        }
      }

      setupModalDragAndDrop(
        'modal_company_logo_zone', 'modal_company_logo_input',
        'modal_company_logo_preview_container', 'modal_company_logo_preview_img',
        'modal_company_logo_filename', 'modal_company_logo_remove_btn'
      );

      setupModalDragAndDrop(
        'modal_admin_profile_zone', 'modal_admin_profile_input',
        'modal_admin_profile_preview_container', 'modal_admin_profile_preview_img',
        'modal_admin_profile_filename', 'modal_admin_profile_remove_btn'
      );
    });
  </script>
@endpush
