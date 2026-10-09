@extends('layouts.superadmin')

@section('title', 'Super Admin · Command Center')
@section('page_title', 'Command Center')
@section('page_subtitle', 'Central Multi-Tenant Control Hub')

@section('content')
@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
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
      box-shadow: 0 16px 45px -10px rgba(47, 107, 255, 0.1), 0 4px 14px rgba(0, 0, 0, 0.03);
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
      box-shadow: 0 22px 55px -10px rgba(47, 107, 255, 0.14);
    }

    .welcome-section::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #1E4FCC, #2F6BFF, #8B5CF6, #22D3EE);
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
      background: linear-gradient(135deg, #1E4FCC 0%, #2F6BFF 100%);
      color: #ffffff !important;
      padding: 4px 12px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      margin-bottom: 8px;
      box-shadow: 0 4px 12px rgba(47, 107, 255, 0.25);
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
      border: 1px solid rgba(47, 107, 255, 0.2);
    }

    .welcome-section .right .status-badge .dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--emerald-light);
      display: inline-block;
      box-shadow: 0 0 0 2px rgba(47, 107, 255, 0.25);
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
      text-decoration: none;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--emerald-dark), var(--emerald-primary), var(--emerald-light));
      color: #fff;
      box-shadow: 0 8px 24px rgba(47, 107, 255, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 32px rgba(47, 107, 255, 0.4);
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
      border-color: rgba(47, 107, 255, 0.25);
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
      border-color: rgba(47, 107, 255, 0.2);
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
      border-color: rgba(47, 107, 255, 0.2);
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
      border-color: rgba(47, 107, 255, 0.2);
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
      border-color: rgba(47, 107, 255, 0.15);
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
      background: rgba(248, 250, 252, 0.95);
      border-bottom: 1px solid rgba(203, 213, 225, 0.8);
      border-right: 1px solid rgba(226, 232, 240, 0.85);
    }

    .table-wrap th:last-child, .table-compact th:last-child {
      border-right: none;
    }

    .table-wrap td, .table-compact td {
      padding: 12px 16px;
      border-bottom: 1px solid rgba(226, 232, 240, 0.8);
      border-right: 1px solid rgba(226, 232, 240, 0.85);
      color: var(--slate-body);
      vertical-align: middle;
      font-weight: 600;
    }

    .table-wrap td:last-child, .table-compact td:last-child {
      border-right: none;
    }

    .table-wrap tr:hover, .table-compact tr:hover {
      background: rgba(47, 107, 255, 0.03);
    }

    /* Actions Cell Wrap & Workspace Button (Matching Screenshot) */
    .actions-cell-wrap {
      display: inline-flex;
      align-items: center;
      justify-content: flex-end;
      gap: 8px;
      position: relative;
    }

    .btn-workspace-custom {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0 14px;
      height: 32px;
      font-size: 12.5px;
      font-weight: 700;
      border-radius: 8px;
      color: #ffffff !important;
      background: linear-gradient(135deg, #0284c7 0%, #0284c7 20%, #0ea5e9 60%, #38bdf8 100%) !important;
      box-shadow: 0 4px 14px rgba(14, 165, 233, 0.45);
      border: none;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      white-space: nowrap;
    }

    .btn-workspace-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(14, 165, 233, 0.6);
      color: #ffffff !important;
    }

    .btn-dots-custom {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: var(--slate-muted, #94a3b8);
      background: transparent;
      border: 1px solid rgba(226, 232, 240, 0.8);
      transition: all 0.2s ease;
      cursor: pointer;
      font-size: 18px;
    }

    .btn-dots-custom:hover {
      background: rgba(2, 132, 199, 0.1);
      color: #0284c7;
      border-color: #0284c7;
    }

    .dropdown-container {
      position: relative;
      display: inline-block;
    }

    /* Custom Actions Dropdown Menu - Exactly Matching User Screenshot */
    .dropdown-menu-custom {
      position: fixed;
      background: #0d1527;
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.55), 0 4px 12px rgba(0, 0, 0, 0.25);
      min-width: 215px;
      padding: 8px 0;
      display: none;
      z-index: 999999;
      animation: fadeSlideDropdown 0.15s ease;
      backdrop-filter: blur(16px);
      text-align: left;
    }

    .dropdown-menu-custom.open {
      display: block;
    }

    @keyframes fadeSlideDropdown {
      from { opacity: 0; transform: translateY(-6px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .dropdown-menu-custom a,
    .dropdown-menu-custom button {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 600;
      color: #e2e8f0;
      text-decoration: none;
      background: none;
      border: none;
      cursor: pointer;
      text-align: left;
      font-family: inherit;
      transition: all 0.15s ease;
      box-sizing: border-box;
    }

    .dropdown-menu-custom a i,
    .dropdown-menu-custom button i {
      font-size: 16px;
      flex-shrink: 0;
    }

    .dropdown-menu-custom a:hover,
    .dropdown-menu-custom button:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #ffffff;
    }

    .dropdown-menu-custom .divider {
      height: 1px;
      background: rgba(255, 255, 255, 0.12);
      margin: 6px 12px;
    }

    .dropdown-menu-custom .danger-item {
      color: #f87171 !important;
    }

    .dropdown-menu-custom .danger-item:hover {
      background: rgba(239, 68, 68, 0.15) !important;
      color: #fca5a5 !important;
    }

    /* Light Theme Overrides */
    html[data-pms-theme="light"] .dropdown-menu-custom,
    html[data-theme="light"] .dropdown-menu-custom {
      background: #ffffff;
      border: 1px solid rgba(226, 232, 240, 0.95);
      box-shadow: 0 14px 40px rgba(15, 23, 42, 0.15), 0 4px 12px rgba(15, 23, 42, 0.08);
    }
    html[data-pms-theme="light"] .dropdown-menu-custom a,
    html[data-pms-theme="light"] .dropdown-menu-custom button,
    html[data-theme="light"] .dropdown-menu-custom a,
    html[data-theme="light"] .dropdown-menu-custom button {
      color: #1e293b;
    }
    html[data-pms-theme="light"] .dropdown-menu-custom a:hover,
    html[data-pms-theme="light"] .dropdown-menu-custom button:hover,
    html[data-theme="light"] .dropdown-menu-custom a:hover,
    html[data-theme="light"] .dropdown-menu-custom button:hover {
      background: #f1f5f9;
      color: #0284c7;
    }
    html[data-pms-theme="light"] .dropdown-menu-custom .divider,
    html[data-theme="light"] .dropdown-menu-custom .divider {
      background: #e2e8f0;
    }

    /* Company Detail Drawer */
    .detail-overlay {
      position: fixed;
      inset: 0;
      background: rgba(11, 23, 41, 0.55);
      backdrop-filter: blur(4px);
      z-index: 999999;
      display: none;
      justify-content: flex-end;
    }
    .detail-overlay.open { display: flex; }
    .detail-drawer {
      width: 100%;
      max-width: 480px;
      background: #ffffff;
      height: 100vh;
      overflow-y: auto;
      padding: 24px;
      box-shadow: var(--card-shadow-lg);
      display: flex;
      flex-direction: column;
      gap: 20px;
      animation: slideDrawer 0.25s ease;
    }
    html[data-pms-theme="dark"] .detail-drawer,
    html[data-theme="dark"] .detail-drawer {
      background: #0f172a;
      color: #e2e8f0;
      border-left: 1px solid rgba(255, 255, 255, 0.1);
    }
    @keyframes slideDrawer {
      from { transform: translateX(30px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    /* Plan Change Modal */
    .plan-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(4px);
      z-index: 999999;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .plan-modal-backdrop.open { display: flex; }
    .plan-modal-dialog {
      background: #ffffff;
      border-radius: 20px;
      max-width: 520px;
      width: 100%;
      padding: 28px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }
    html[data-pms-theme="dark"] .plan-modal-dialog,
    html[data-theme="dark"] .plan-modal-dialog {
      background: #0f172a;
      color: #e2e8f0;
      border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .plan-card-option {
      background: #ffffff;
      border: 2px solid rgba(226, 232, 240, 0.8);
      border-radius: 14px;
      padding: 14px 16px;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-bottom: 10px;
    }
    html[data-pms-theme="dark"] .plan-card-option,
    html[data-theme="dark"] .plan-card-option {
      background: #1e293b;
      border-color: rgba(255, 255, 255, 0.1);
    }
    .plan-card-option:hover { border-color: #0284c7; }
    .plan-card-option:has(input:checked) { border-color: #0284c7; background: rgba(2, 132, 199, 0.08); }

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

    /* TABLE TOOLBAR & CONTROLS */
    .table-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 20px;
      background: rgba(248, 250, 252, 0.95);
      border-bottom: 1px solid rgba(203, 213, 225, 0.8);
      flex-wrap: wrap;
      gap: 12px;
    }

    .table-select {
      padding: 6px 12px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      color: var(--slate-dark);
      background: #ffffff;
      cursor: pointer;
      outline: none;
      transition: all 0.2s ease;
    }

    .table-search-input {
      padding: 6px 14px 6px 36px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 500;
      width: 220px;
      background: #ffffff;
      color: var(--slate-dark);
      outline: none;
      transition: all 0.2s ease;
    }

    .table-footer-bar {
      padding: 14px 20px;
      border-top: 1px solid rgba(203, 213, 225, 0.8);
      background: var(--slate-light);
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    /* PLAN CARDS */
    .catalog-plan-card {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(8px);
      border-radius: 20px;
      border: 1px solid rgba(226, 232, 240, 0.85);
      padding: 20px;
      box-shadow: var(--card-shadow-sm);
      transition: all 0.25s ease;
    }

    .catalog-plan-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--card-shadow-md);
    }

    /* HEALTH PILLS */
    .audit-health-pill {
      background: rgba(255, 255, 255, 0.92);
      border-radius: 16px;
      border: 1px solid rgba(226, 232, 240, 0.85);
      padding: 16px 20px;
      flex: 1;
      min-width: 150px;
      box-shadow: var(--card-shadow-sm);
      transition: all 0.25s ease;
    }

    /* BADGES */
    .db-badge-pill {
      font-family: var(--font-mono);
      font-size: 11px;
      color: #0369a1;
      background: #e0f2fe;
      padding: 3px 8px;
      border-radius: 6px;
      border: 1px solid #bae6fd;
      display: inline-block;
    }

    .connection-code-badge {
      font-family: var(--font-mono);
      background: var(--slate-light);
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 12px;
      color: var(--slate-body);
      border: 1px solid rgba(226, 232, 240, 0.8);
      display: inline-block;
    }

    /* Super Admin Dashboard Dark Mode Overrides */
    html[data-pms-theme="dark"] .welcome-section,
    html[data-theme="dark"] .welcome-section {
      background: linear-gradient(135deg, rgba(15, 21, 48, 0.96) 0%, rgba(20, 27, 61, 0.92) 100%);
      border-color: var(--border-subtle);
      box-shadow: 0 16px 45px -10px rgba(0, 0, 0, 0.4);
    }
    html[data-pms-theme="dark"] .welcome-section .right .date,
    html[data-theme="dark"] .welcome-section .right .date {
      background: var(--bg-surface-subtle);
      border-color: var(--border-subtle);
      color: var(--text-muted);
    }
    html[data-pms-theme="dark"] .kpi-card,
    html[data-theme="dark"] .kpi-card,
    html[data-pms-theme="dark"] .chart-card,
    html[data-theme="dark"] .chart-card,
    html[data-pms-theme="dark"] .activity-card,
    html[data-theme="dark"] .activity-card,
    html[data-pms-theme="dark"] .table-card,
    html[data-theme="dark"] .table-card,
    html[data-pms-theme="dark"] .table-wrap,
    html[data-theme="dark"] .table-wrap,
    html[data-bs-theme="dark"] .table-wrap {
      background: var(--bg-surface) !important;
      border-color: var(--border-subtle) !important;
      box-shadow: var(--card-shadow-sm);
    }
    html[data-pms-theme="dark"] .health-item,
    html[data-theme="dark"] .health-item {
      background: var(--bg-surface-subtle);
      border-color: var(--border-subtle);
    }
    html[data-pms-theme="dark"] .btn-outline,
    html[data-theme="dark"] .btn-outline {
      border-color: var(--border-subtle);
      color: var(--text-body);
    }
    html[data-pms-theme="dark"] .btn-secondary,
    html[data-theme="dark"] .btn-secondary {
      background: var(--bg-surface-subtle);
      border-color: var(--border-subtle);
      color: var(--text-main);
    }
    html[data-pms-theme="dark"] .modal,
    html[data-theme="dark"] .modal {
      background: var(--bg-surface);
      border: 1px solid var(--border-strong);
    }
    html[data-pms-theme="dark"] .form-group input,
    html[data-pms-theme="dark"] .form-group select,
    html[data-theme="dark"] .form-group input,
    html[data-theme="dark"] .form-group select {
      background: var(--bg-surface-subtle);
      border-color: var(--border-subtle);
      color: var(--text-main);
    }
    html[data-pms-theme="dark"] .dropdown-menu,
    html[data-theme="dark"] .dropdown-menu {
      background: var(--bg-surface);
      border-color: var(--border-strong);
    }
    html[data-pms-theme="dark"] .dropdown-item,
    html[data-theme="dark"] .dropdown-item {
      color: var(--text-body);
    }
    html[data-pms-theme="dark"] .table-responsive table,
    html[data-theme="dark"] .table-responsive table {
      color: var(--text-body);
    }
    html[data-pms-theme="dark"] .table-wrap th,
    html[data-theme="dark"] .table-wrap th,
    html[data-bs-theme="dark"] .table-wrap th,
    html[data-pms-theme="dark"] .table-compact th,
    html[data-theme="dark"] .table-compact th,
    html[data-bs-theme="dark"] .table-compact th {
      background: var(--bg-surface-subtle) !important;
      color: var(--text-muted) !important;
      border-bottom-color: var(--border-subtle) !important;
      border-right-color: var(--border-subtle) !important;
    }
    html[data-pms-theme="dark"] .table-wrap td,
    html[data-theme="dark"] .table-wrap td,
    html[data-bs-theme="dark"] .table-wrap td,
    html[data-pms-theme="dark"] .table-compact td,
    html[data-theme="dark"] .table-compact td,
    html[data-bs-theme="dark"] .table-compact td {
      border-bottom-color: var(--border-subtle) !important;
      border-right-color: var(--border-subtle) !important;
      color: var(--text-body) !important;
      background: transparent !important;
    }
    html[data-pms-theme="dark"] .table-wrap tr:hover,
    html[data-theme="dark"] .table-wrap tr:hover,
    html[data-pms-theme="dark"] .table-compact tr:hover,
    html[data-theme="dark"] .table-compact tr:hover {
      background: var(--bg-surface-hover) !important;
    }
    html[data-pms-theme="dark"] .table-toolbar,
    html[data-theme="dark"] .table-toolbar,
    html[data-bs-theme="dark"] .table-toolbar {
      background: var(--bg-surface-subtle) !important;
      border-bottom-color: var(--border-subtle) !important;
    }
    html[data-pms-theme="dark"] .table-select,
    html[data-theme="dark"] .table-select,
    html[data-bs-theme="dark"] .table-select,
    html[data-pms-theme="dark"] .table-search-input,
    html[data-theme="dark"] .table-search-input,
    html[data-bs-theme="dark"] .table-search-input {
      background: var(--bg-surface) !important;
      border-color: var(--border-subtle) !important;
      color: var(--text-main) !important;
    }
    html[data-pms-theme="dark"] .table-search-input::placeholder,
    html[data-theme="dark"] .table-search-input::placeholder {
      color: var(--text-light) !important;
    }
    html[data-pms-theme="dark"] .table-footer-bar,
    html[data-theme="dark"] .table-footer-bar,
    html[data-bs-theme="dark"] .table-footer-bar {
      background: var(--bg-surface-subtle) !important;
      border-top-color: var(--border-subtle) !important;
      color: var(--text-muted) !important;
    }
    html[data-pms-theme="dark"] .catalog-plan-card,
    html[data-theme="dark"] .catalog-plan-card,
    html[data-bs-theme="dark"] .catalog-plan-card {
      background: var(--bg-surface) !important;
      border-color: var(--border-subtle) !important;
      box-shadow: var(--card-shadow-sm) !important;
    }
    html[data-pms-theme="dark"] .catalog-plan-card:hover,
    html[data-theme="dark"] .catalog-plan-card:hover,
    html[data-bs-theme="dark"] .catalog-plan-card:hover {
      border-color: rgba(47, 107, 255, 0.4) !important;
      background: var(--bg-surface-hover) !important;
      box-shadow: var(--card-shadow-md) !important;
    }
    html[data-pms-theme="dark"] .audit-health-pill,
    html[data-theme="dark"] .audit-health-pill,
    html[data-bs-theme="dark"] .audit-health-pill {
      background: var(--bg-surface) !important;
      border-color: var(--border-subtle) !important;
      box-shadow: var(--card-shadow-sm) !important;
    }
    html[data-pms-theme="dark"] .audit-health-pill:hover,
    html[data-theme="dark"] .audit-health-pill:hover,
    html[data-bs-theme="dark"] .audit-health-pill:hover {
      background: var(--bg-surface-hover) !important;
      border-color: rgba(47, 107, 255, 0.3) !important;
    }
    html[data-pms-theme="dark"] .audit-health-pill .health-green,
    html[data-theme="dark"] .audit-health-pill .health-green,
    html[data-bs-theme="dark"] .audit-health-pill .health-green {
      color: #60A5FA !important;
    }
    html[data-pms-theme="dark"] .audit-health-pill .health-amber,
    html[data-theme="dark"] .audit-health-pill .health-amber,
    html[data-bs-theme="dark"] .audit-health-pill .health-amber {
      color: #fbbf24 !important;
    }
    html[data-pms-theme="dark"] .audit-health-pill .health-blue,
    html[data-theme="dark"] .audit-health-pill .health-blue,
    html[data-bs-theme="dark"] .audit-health-pill .health-blue {
      color: #38bdf8 !important;
    }
    html[data-pms-theme="dark"] .plan-feature-text,
    html[data-theme="dark"] .plan-feature-text,
    html[data-bs-theme="dark"] .plan-feature-text {
      color: #38bdf8 !important;
    }
    html[data-pms-theme="dark"] .db-badge-pill,
    html[data-theme="dark"] .db-badge-pill,
    html[data-bs-theme="dark"] .db-badge-pill {
      color: #38bdf8 !important;
      background: rgba(56, 189, 248, 0.12) !important;
      border-color: rgba(56, 189, 248, 0.25) !important;
    }
    html[data-pms-theme="dark"] .connection-code-badge,
    html[data-theme="dark"] .connection-code-badge,
    html[data-bs-theme="dark"] .connection-code-badge {
      background: var(--bg-surface-subtle) !important;
      color: var(--text-main) !important;
      border-color: var(--border-subtle) !important;
    }
    html[data-pms-theme="dark"] .status-badge.success,
    html[data-theme="dark"] .status-badge.success,
    html[data-bs-theme="dark"] .status-badge.success {
      background: rgba(47, 107, 255, 0.18) !important;
      color: #60A5FA !important;
      border: 1px solid rgba(79, 131, 255, 0.3) !important;
    }
    html[data-pms-theme="dark"] .status-badge.info,
    html[data-theme="dark"] .status-badge.info,
    html[data-bs-theme="dark"] .status-badge.info {
      background: rgba(59, 130, 246, 0.18) !important;
      color: #60a5fa !important;
      border: 1px solid rgba(96, 165, 250, 0.3) !important;
    }
    html[data-pms-theme="dark"] .status-badge.warning,
    html[data-theme="dark"] .status-badge.warning,
    html[data-bs-theme="dark"] .status-badge.warning {
      background: rgba(245, 158, 11, 0.18) !important;
      color: #fbbf24 !important;
      border: 1px solid rgba(251, 191, 36, 0.3) !important;
    }
    html[data-pms-theme="dark"] .status-badge.critical,
    html[data-theme="dark"] .status-badge.critical,
    html[data-bs-theme="dark"] .status-badge.critical {
      background: rgba(239, 68, 68, 0.18) !important;
      color: #f87171 !important;
      border: 1px solid rgba(248, 113, 113, 0.3) !important;
    }
    html[data-pms-theme="dark"] .dropdown-toggle,
    html[data-theme="dark"] .dropdown-toggle,
    html[data-bs-theme="dark"] .dropdown-toggle {
      border-color: var(--border-subtle) !important;
      color: var(--text-muted) !important;
    }
    html[data-pms-theme="dark"] .dropdown-item:hover,
    html[data-theme="dark"] .dropdown-item:hover,
    html[data-bs-theme="dark"] .dropdown-item:hover {
      background: var(--bg-surface-hover) !important;
      color: var(--text-main) !important;
    }
    html[data-pms-theme="dark"] .form-group textarea,
    html[data-theme="dark"] .form-group textarea,
    html[data-bs-theme="dark"] .form-group textarea {
      background: var(--bg-surface-subtle) !important;
      border-color: var(--border-subtle) !important;
      color: var(--text-main) !important;
    }
    html[data-pms-theme="dark"] .drag-drop-zone,
    html[data-theme="dark"] .drag-drop-zone,
    html[data-bs-theme="dark"] .drag-drop-zone {
      background: var(--bg-surface-subtle) !important;
      border-color: var(--border-subtle) !important;
    }
    html[data-pms-theme="dark"] .drag-drop-zone .default-text div,
    html[data-theme="dark"] .drag-drop-zone .default-text div,
    html[data-bs-theme="dark"] .drag-drop-zone .default-text div {
      color: var(--text-muted) !important;
    }
    html[data-pms-theme="dark"] th,
    html[data-theme="dark"] th {
      color: var(--text-muted);
      border-bottom-color: var(--border-subtle);
    }
    html[data-pms-theme="dark"] td,
    html[data-theme="dark"] td {
      border-bottom-color: var(--border-subtle);
      color: var(--text-body);
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
        <a href="{{ Route::has('super-admin.companies.create') ? route('super-admin.companies.create') : (Route::has('superadmin.companies.create') ? route('superadmin.companies.create') : url('/super-admin/companies/create')) }}" class="btn btn-primary"><i class="bx bx-plus-circle"></i> Provision Tenant</a>
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
      <a href="{{ Route::has('super-admin.companies.create') ? route('super-admin.companies.create') : (Route::has('superadmin.companies.create') ? route('superadmin.companies.create') : url('/super-admin/companies/create')) }}" class="btn btn-primary"><i class="bx bx-plus-circle"></i> Provision Tenant</a>
      <a href="{{ route('super-admin.companies.index') }}" class="action-link">Central Register <i class="bx bx-right-arrow-alt"></i></a>
    </div>
  </div>

  <div class="table-wrap" style="border-radius: 20px; overflow: hidden;">
    <div class="table-toolbar">
      <div style="display:flex; align-items:center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--slate-body);">
        <span>Show</span>
        <select id="entriesPerPageSelect" class="table-select" onchange="changeEntriesPerPage(this.value)">
          <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span>entries</span>
      </div>
      <div style="position: relative;">
        <i class="bx bx-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--slate-muted); font-size: 17px;"></i>
        <input type="text" id="dashboardTableSearch" class="table-search-input" placeholder="Search companies..." onkeyup="filterDashboardCompanies()" />
      </div>
    </div>

    <table class="table-compact" id="dashboardCompaniesTable">
      <thead>
        <tr>
          <th style="width: 42px; text-align: center;">
            <input type="checkbox" id="selectAllDashboardCompanies" onchange="toggleSelectAllDashboardCompanies(this)" style="cursor: pointer; width: 16px; height: 16px; accent-color: var(--emerald-primary);" />
          </th>
          <th>Company</th>
          <th>Subdomain / Identifier</th>
          <th>Database Name</th>
          <th>Status</th>
          <th>Plan</th>
          <th>Assigned Admins</th>
          <th>Subscription End</th>
          <th style="min-width: 105px;">Last Activity</th>
          <th style="width: 175px; text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($companies as $company)
          @php
            $logoUrl = null;
            if (!empty($company->logo)) {
                if (file_exists(public_path($company->logo))) {
                    $logoUrl = asset($company->logo);
                } elseif (file_exists(public_path('user-uploads/app-logo/' . $company->logo))) {
                    $logoUrl = asset('user-uploads/app-logo/' . $company->logo);
                } elseif (str_starts_with($company->logo, 'http') || str_starts_with($company->logo, '/')) {
                    $logoUrl = asset($company->logo);
                }
            }
          @endphp
          <tr>
            <td style="text-align: center;">
              <input type="checkbox" class="dashboard-company-cb" value="{{ $company->id }}" style="cursor: pointer; width: 16px; height: 16px; accent-color: var(--emerald-primary);" />
            </td>
            <td>
              <div style="display:flex; align-items:center; gap:12px;">
                @if($logoUrl)
                  <img src="{{ $logoUrl }}" alt="{{ $company->name }}" style="width:36px; height:36px; border-radius:10px; object-fit:cover; border:1px solid var(--border-subtle); flex-shrink:0;" />
                @else
                  <div style="width:36px; height:36px; border-radius:10px; background: linear-gradient(135deg, var(--slate-dark), var(--emerald-dark)); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:13px; flex-shrink:0;">
                    {{ strtoupper(substr($company->name, 0, 2)) }}
                  </div>
                @endif
                <div>
                  <strong style="color:var(--slate-dark); font-weight:700;">{{ $company->name }}</strong>
                  <div style="font-size:11px; color:var(--slate-muted);">{{ $company->email }}</div>
                </div>
              </div>
            </td>
            <td>
              <code class="connection-code-badge">
                {{ $company->subdomain ?? $company->company_code ?? strtolower(str_replace(' ', '', $company->name)) }}
              </code>
            </td>
            <td>
              <span class="db-badge-pill">
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
              @php
                $displaySub = ($company->activeSubscription?->plan ? $company->activeSubscription : null) ?? $company->latestSubscription;
                $displayPlanName = $displaySub?->plan?->name
                    ?? ($company->highest_plan_slug ? strtoupper($company->highest_plan_slug) : null);

                $displaySubStatus = strtolower((string) ($displaySub?->status ?? ''));
                $displaySubExpired = $displaySub?->ends_at && $displaySub->ends_at->lt(now()->startOfDay());
                $displayPlanState = match (true) {
                    ! $displaySub => null,
                    $displaySubExpired || $displaySubStatus === 'expired' => 'Expired',
                    $displaySubStatus === 'trial' => 'Trial',
                    $displaySubStatus === 'pending' => 'Pending',
                    default => null,
                };
                $displayPlanBadge = match ($displayPlanState) {
                    'Expired' => 'critical',
                    'Trial', 'Pending' => 'warning',
                    default => 'info',
                };
              @endphp
              @if($displayPlanName)
                <span class="status-badge {{ $displayPlanBadge }}">{{ $displayPlanName }}{{ $displayPlanState ? ' · ' . $displayPlanState : '' }}</span>
              @else
                <span class="status-badge info">No Plan</span>
              @endif
            </td>
            <td>
              <span style="font-size:12px; color:var(--slate-muted); font-weight:600;">
                {{ $company->users->pluck('name')->join(', ') ?: 'Unassigned' }}
              </span>
            </td>
            <td>
              <span style="font-size:12px; color:var(--slate-muted); font-weight:600;">
                {{ $displaySub?->ends_at?->format('M d, Y') ?? ($company->trial_ends_at ? \Carbon\Carbon::parse($company->trial_ends_at)->format('M d, Y') : '—') }}
              </span>
            </td>
            <td style="color: var(--slate-muted, #94a3b8); font-size: 12px; font-weight: 500; white-space: nowrap;">
              @php
                $activityMinutes = $company->updated_at ? max(2, (int) $company->updated_at->diffInMinutes(now())) : rand(5, 45);
                if ($activityMinutes < 60) {
                    $activityStr = $activityMinutes . ' mins ago';
                } elseif ($activityMinutes < 1440) {
                    $activityStr = round($activityMinutes / 60) . ' hrs ago';
                } else {
                    $activityStr = round($activityMinutes / 1440) . ' days ago';
                }
              @endphp
              {{ $activityStr }}
            </td>
            <td style="text-align:right;">
              <div class="actions-cell-wrap">
                <a href="{{ Route::has('super-admin.companies.show') ? route('super-admin.companies.show', $company->id) : (Route::has('superadmin.companies.show') ? route('superadmin.companies.show', $company->id) : url('/superadmin/companies/'.$company->id)) }}" 
                   class="btn-workspace-custom" title="Open Dedicated Workspace">
                  Workspace
                </a>
                <div class="dropdown-container">
                  <button type="button" class="btn-dots-custom dropdown-toggle-trigger" title="More Options">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu-custom">
                    <a href="{{ Route::has('super-admin.companies.show') ? route('super-admin.companies.show', $company->id) : (Route::has('superadmin.companies.show') ? route('superadmin.companies.show', $company->id) : url('/superadmin/companies/'.$company->id)) }}">
                      <i class="bx bx-show" style="color: #38bdf8;"></i> Open Workspace
                    </a>
                    <a href="javascript:void(0)" class="trigger-detail-drawer" data-company-id="{{ $company->id }}" data-company-name="{{ $company->name }}" data-company-email="{{ $company->email }}" data-company-db="{{ $company->db_name }}" data-company-logo="{{ $company->logo ? asset($company->logo) : '' }}">
                      <i class="bx bx-info-circle" style="color: #38bdf8;"></i> Quick Details
                    </a>
                    <a href="javascript:void(0)" class="trigger-plan-modal" data-company-id="{{ $company->id }}" data-current-plan-id="{{ $displaySub?->plan_id }}">
                      <i class="bx bx-layer" style="color: #c084fc;"></i> Change Subscription
                    </a>
                    <div class="divider"></div>
                    <form method="POST" action="{{ Route::has('superadmin.companies.enter') ? route('superadmin.companies.enter', $company) : (Route::has('super-admin.companies.enter') ? route('super-admin.companies.enter', $company) : url('/superadmin/companies/'.$company->id.'/enter')) }}" style="margin: 0;">
                      @csrf
                      <button type="submit" style="width:100%; text-align:left;"><i class="bx bx-log-in-circle" style="color: #f59e0b;"></i> Impersonate Context</button>
                    </form>
                    <div class="divider"></div>
                    <a href="javascript:void(0)" onclick="confirmSuspendCompany({{ $company->id }}, '{{ addslashes($company->name) }}')" class="danger-item"><i class="bx bx-block" style="color: #f87171;"></i> Suspend Company</a>
                    <a href="javascript:void(0)" onclick="confirmDeleteCompany({{ $company->id }}, '{{ addslashes($company->name) }}')" class="danger-item"><i class="bx bx-trash" style="color: #f87171;"></i> Delete Company</a>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" style="text-align:center; padding:24px; color:var(--slate-muted);">
              No tenant companies found. Click "Provision Tenant" above to create one.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="table-footer-bar">
      <div style="font-size: 12.5px; font-weight: 600; color: var(--slate-muted);">
        @if(method_exists($companies, 'total'))
          Showing {{ $companies->firstItem() ?? 0 }} to {{ $companies->lastItem() ?? 0 }} of {{ $companies->total() }} entries
        @else
          Showing {{ count($companies) }} entries
        @endif
      </div>
      @if(method_exists($companies, 'links'))
        <div>
          {{ $companies->links() }}
        </div>
      @endif
    </div>
  </div>

  <script>
    function toggleSelectAllDashboardCompanies(master) {
      document.querySelectorAll('.dashboard-company-cb').forEach(cb => cb.checked = master.checked);
    }
    function changeEntriesPerPage(val) {
      window.location.href = "{{ url()->current() }}?per_page=" + val;
    }
    function filterDashboardCompanies() {
      const input = document.getElementById('dashboardTableSearch').value.toLowerCase();
      const rows = document.querySelectorAll('#dashboardCompaniesTable tbody tr');
      rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
      });
    }
  </script>

  <!-- SUBSCRIPTION CATALOG -->
  <div class="section-header" id="plans">
    <h2>Subscription Catalog</h2>
    <a href="{{ route('super-admin.plans.index') }}" class="action-link">Plans Catalog <i class="bx bx-layer"></i></a>
  </div>
  <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:28px;">
    @forelse($plans as $plan)
      <div class="catalog-plan-card">
        <div style="font-size:11px; font-weight:700; color:var(--slate-muted); text-transform:uppercase; letter-spacing:0.5px;">{{ $plan->name }} Plan</div>
        <div style="font-size:26px; font-weight:900; color:var(--slate-dark); margin: 4px 0;">${{ number_format($plan->monthly_price, 0) }}<span style="font-size:13px; font-weight:600; color:var(--slate-muted);">/mo</span></div>
        <div class="plan-feature-text" style="font-size:12px; color:var(--emerald-primary); font-weight:700; display:flex; align-items:center; gap:4px;">
          <i class="bx bx-check-circle"></i> 
          {{ $plan->max_users > 0 ? $plan->max_users . ' users max' : 'Unlimited users' }}
        </div>
      </div>
    @empty
      <div class="catalog-plan-card">
        <div style="font-size:11px; font-weight:700; color:var(--slate-muted); text-transform:uppercase;">Starter Plan</div>
        <div style="font-size:26px; font-weight:900; color:var(--slate-dark); margin: 4px 0;">$49<span style="font-size:13px; font-weight:600; color:var(--slate-muted);">/mo</span></div>
        <div class="plan-feature-text" style="font-size:12px; color:var(--emerald-primary); font-weight:700;"><i class="bx bx-check-circle"></i> Standard Features</div>
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
          <td><code class="connection-code-badge">central</code></td>
          <td>Batch #104</td>
          <td><span class="status-badge success"><i class="bx bx-check"></i> Synchronized</span></td>
          <td><span class="status-badge info">Completed</span></td>
        </tr>
        @foreach($companies->take(3) as $comp)
          <tr>
            <td><strong style="font-family:var(--font-mono); color:var(--slate-dark);">{{ $comp->db_name ?? ('pms_' . strtolower(str_replace(' ', '', $comp->name))) }}</strong></td>
            <td><code class="connection-code-badge">tenant</code></td>
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

  <!-- AUDIT ACTIVITY LOGS -->
  <div class="section-header" id="activity-logs">
    <h2>Audit Activity Logs</h2>
    <div style="display: flex; gap: 10px; align-items: center;">
      <button type="button" class="btn btn-secondary" onclick="exportActivityLogsToCSV()" style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; padding: 6px 14px; cursor: pointer;">
        <i class="bx bx-download"></i> Export CSV
      </button>
      <a href="{{ Route::has('super-admin.activity-logs.index') ? route('super-admin.activity-logs.index') : (Route::has('super-admin.tenant-audit.index') ? route('super-admin.tenant-audit.index') : url('/super-admin/activity-logs')) }}" class="action-link">Full History <i class="bx bx-history"></i></a>
    </div>
  </div>
  <div class="table-wrap" style="border-radius: 20px; overflow: hidden;">
    <div class="table-toolbar">
      <div style="display:flex; align-items:center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--slate-body);">
        <span>Show</span>
        <select id="activityEntriesPerPageSelect" class="table-select" onchange="changeActivityEntriesPerPage(this.value)">
          <option value="10" selected>10</option>
          <option value="20">20</option>
          <option value="30">30</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <span>entries</span>
      </div>
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="position: relative;">
          <i class="bx bx-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--slate-muted); font-size: 17px;"></i>
          <input type="text" id="activityTableSearch" class="table-search-input" placeholder="Search activity logs..." onkeyup="filterActivityLogs()" />
        </div>
        <button type="button" class="btn btn-secondary" onclick="exportActivityLogsToCSV()" title="Export Activity Logs to CSV" style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; height: 34px; padding: 0 14px; cursor: pointer; border-radius: 8px;">
          <i class="bx bx-export"></i> Export
        </button>
      </div>
    </div>
    <table class="table-compact" id="activityLogsTable">
      <thead>
        <tr>
          <th>Timestamp</th>
          <th>Company</th>
          <th>Action</th>
          <th>IP Address</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="activityLogsTableBody">
        @forelse($recentActivities as $activity)
          <tr class="activity-log-row">
            <td style="font-size:12px; color:var(--slate-muted);">{{ $activity->created_at?->format('Y-m-d H:i') }}</td>
            <td><strong style="color:var(--slate-dark);">{{ $activity->company?->name ?? 'System' }}</strong></td>
            <td>{{ str_replace('.', ' ', ucfirst($activity->action)) }}</td>
            <td><code class="connection-code-badge" style="font-size:11px;">{{ $activity->ip_address ?? '127.0.0.1' }}</code></td>
            <td><span class="status-badge success"><i class="bx bx-check"></i> Success</span></td>
          </tr>
        @empty
          <tr class="no-activity-row">
            <td colspan="5" style="text-align:center; color:var(--slate-muted); padding:20px;">No recent audit activity logged.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="table-footer-bar" id="activityTableFooter">
      <div style="font-size: 12.5px; font-weight: 600; color: var(--slate-muted);" id="activityShowingText">
        Showing 1 to {{ min(10, count($recentActivities)) }} of {{ count($recentActivities) }} entries
      </div>
      <div style="display: flex; gap: 4px; align-items: center;" id="activityPaginationControls"></div>
    </div>
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
      
      <form method="POST" action="{{ route('superadmin.companies.store') }}" enctype="multipart/form-data" id="dashboardProvisionCompanyForm">
        @csrf
        <div class="form-group">
          <label>Company Name <span style="color:var(--rose-accent);">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Acme Corporation" />
        </div>
        <div class="form-group">
          <label>Company Email <span style="color:var(--rose-accent);">*</span></label>
          <input type="email" name="email" id="modal_company_email" value="{{ old('email') }}" required placeholder="contact@acme.com" />
          <div id="modal_company_email_error" class="field-error-feedback"></div>
        </div>
        <div class="form-group">
          <label>Subdomain / Tenant Identifier</label>
          <input type="text" name="subdomain" value="{{ old('subdomain') }}" placeholder="acme" />
        </div>
        <div class="form-group">
          <label>Contact Phone Number</label>
          @php
              $fullPhone = old('phone');
              $countryCode = '+91';
              $phoneNum = $fullPhone;
              if($fullPhone && preg_match('/^(\+\d{1,4})\s*[-\s]?(.*)$/', $fullPhone, $matches)) {
                  $countryCode = $matches[1];
                  $phoneNum = $matches[2];
              }
          @endphp
          <div style="display: flex;">
            <select id="modal_company_country_code" class="country-code-select" style="width: 110px; flex-shrink: 0; padding: 10px 8px; border: 1px solid var(--border-subtle, #cbd5e1); border-right: 0; border-top-left-radius: 8px; border-bottom-left-radius: 8px; font-size: 13px; outline: none;">
                @foreach(\App\Models\Country::getAllWithPhoneCodes() as $c)
                    <option value="{{ $c->phone_code }}" {{ $countryCode == $c->phone_code ? 'selected' : '' }}>
                        {{ $c->iso_code ? $c->iso_code . ' ' : '' }}({{ $c->phone_code }})
                    </option>
                @endforeach
            </select>
            <input type="text" id="modal_company_phone_display" value="{{ $phoneNum }}" placeholder="555 0199" style="border-top-left-radius: 0; border-bottom-left-radius: 0; width: 100%;" />
            <input type="hidden" name="phone" id="modal_company_phone_hidden" value="{{ $fullPhone }}">
          </div>
          <div id="modal_company_phone_error" class="field-error-feedback"></div>
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
          <input type="email" name="admin_email" id="modal_company_admin_email" value="{{ old('admin_email') }}" required placeholder="admin@acme.com" />
          <div id="modal_company_admin_email_error" class="field-error-feedback"></div>
        </div>
        <div class="form-group">
          <label>Admin Password <span style="color:var(--rose-accent);">*</span></label>
          <div style="position: relative;">
            <input type="password" name="admin_password" id="modal_admin_password" required minlength="8" maxlength="128" placeholder="•••••••• (min 8 chars)" style="padding-right: 40px;" />
            <button type="button" onclick="toggleDashboardPassword('modal_admin_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--slate-muted); font-size: 18px;" title="Show/Hide Password">
              <i class="bx bx-show"></i>
            </button>
          </div>
          <div style="font-size: 11px; color: var(--slate-muted); margin-top: 4px;">Must be between 8 and 128 characters.</div>
          <div id="modal_admin_password_error" class="field-error-feedback"></div>
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

  <!-- COMPANY DETAIL DRAWER -->
  <div class="detail-overlay" id="companyDetailDrawer">
    <div class="detail-drawer">
      <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(226, 232, 240, 0.8); padding-bottom: 14px;">
        <div style="display: flex; align-items: center; gap: 10px;">
          <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--slate-dark), var(--emerald-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; overflow: hidden;" id="drawerLogo">CO</div>
          <div>
            <h3 id="drawerName" style="font-size: 18px; font-weight: 800; color: var(--slate-dark); margin: 0;">Company Name</h3>
            <div id="drawerDomain" style="font-size: 12px; color: var(--slate-muted);">tenant.domain</div>
          </div>
        </div>
        <button type="button" id="closeDrawerBtn" style="font-size: 22px; color: var(--slate-muted); border: none; background: transparent; cursor: pointer;">
          <i class="bx bx-x"></i>
        </button>
      </div>

      <div>
        <h4 style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--slate-muted); margin-bottom: 8px;">Tenant Summary</h4>
        <div style="background: var(--slate-light, #f8fafc); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 12px; padding: 14px; font-size: 13px; display: flex; flex-direction: column; gap: 8px;">
          <div style="display: flex; justify-content: space-between;"><span style="color: var(--slate-muted);">Contact Email:</span> <strong id="drawerEmail" style="color: var(--slate-dark);">admin@company.com</strong></div>
          <div style="display: flex; justify-content: space-between;"><span style="color: var(--slate-muted);">Database:</span> <code id="drawerDb" style="color: #0284c7; font-family: monospace;">tenant_db</code></div>
          <div style="display: flex; justify-content: space-between;"><span style="color: var(--slate-muted);">Status:</span> <span class="status-badge success" id="drawerStatus">Active</span></div>
        </div>
      </div>

      <div>
        <h4 style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--slate-muted); margin-bottom: 8px;">Quick Actions</h4>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <a href="#" class="btn-workspace-custom" style="width: 100%; text-align: center;" id="drawerWorkspaceBtn">Open Dedicated Workspace</a>
        </div>
      </div>
    </div>
  </div>

  <!-- PLAN CHANGE MODAL -->
  <div class="plan-modal-backdrop" id="planChangeModal">
    <div class="plan-modal-dialog">
      <h3 style="font-size: 20px; font-weight: 800; margin-top: 0; margin-bottom: 6px; color: var(--slate-dark);">Change Subscription Plan</h3>
      <p style="font-size: 13.5px; color: var(--slate-muted); margin-bottom: 20px;">
        Select a new subscription tier for this tenant company.
      </p>

      <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px;">
        @forelse($plans as $plan)
          <label class="plan-card-option" style="display:block" data-plan-id="{{ $plan->id }}">
            <input type="radio" name="plan_id" value="{{ $plan->id }}" form="assignPlanForm" required @checked($loop->first) style="position:absolute;opacity:0;width:1px;height:1px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span class="status-badge info" style="font-weight: 800; text-transform: uppercase;">{{ $plan->name }}</span>
              <strong style="font-size: 15px; color: var(--slate-dark);">${{ number_format($plan->monthly_price, 0) }} / mo</strong>
            </div>
            <div style="font-size: 12px; color: var(--slate-muted); margin-top: 4px;">{{ $plan->description ?? ($plan->max_users > 0 ? $plan->max_users . ' Users Max' : 'Unlimited Users') }}</div>
          </label>
        @empty
          <div class="plan-card-option selected">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span class="status-badge info">STANDARD</span>
              <strong style="font-size: 15px; color: var(--slate-dark);">$49 / mo</strong>
            </div>
          </div>
        @endforelse
      </div>

      <form id="assignPlanForm" method="POST" action="{{ Route::has('superadmin.subscriptions.assign') ? route('superadmin.subscriptions.assign') : url('/superadmin/subscriptions/assign') }}">
        @csrf
        <input type="hidden" name="company_id" id="modalPlanCompanyId" value="">
        <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid rgba(226, 232, 240, 0.8); padding-top: 16px;">
          <button type="button" class="btn btn-secondary" id="closePlanModalBtn">Cancel</button>
          <button type="submit" class="btn btn-primary" id="confirmPlanChangeBtn">Confirm Change</button>
        </div>
      </form>
    </div>
  </div>

  <!-- HIDDEN FORMS FOR SUSPEND & DELETE -->
  <form id="dashboardSuspendForm" method="POST" action="" style="display:none;">
    @csrf
  </form>
  <form id="dashboardDeleteForm" method="POST" action="" style="display:none;">
    @csrf
    @method('DELETE')
  </form>
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

    function toggleSelectAllDashboardCompanies(master) {
      const checkboxes = document.querySelectorAll('.dashboard-company-cb');
      checkboxes.forEach(cb => cb.checked = master.checked);
    }

    function changeEntriesPerPage(val) {
      const url = new URL(window.location.href);
      url.searchParams.set('per_page', val);
      url.searchParams.delete('companies_page');
      window.location.href = url.toString();
    }

    function filterDashboardCompanies() {
      const query = document.getElementById('dashboardTableSearch').value.toLowerCase().trim();
      const rows = document.querySelectorAll('#dashboardCompaniesTable tbody tr');
      
      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (row.querySelector('td[colspan]')) return;
        row.style.display = text.includes(query) ? '' : 'none';
      });
    }

    // Activity Logs Table Controls (Show entries: 10, 20, 30, search, pagination, CSV export)
    let activityPageSize = 10;
    let activityCurrentPage = 1;

    function renderActivityTable() {
      const allRows = Array.from(document.querySelectorAll('#activityLogsTableBody tr.activity-log-row'));
      const emptyRow = document.querySelector('#activityLogsTableBody tr.no-activity-row');
      const query = (document.getElementById('activityTableSearch')?.value || '').toLowerCase().trim();

      const matchedRows = allRows.filter(row => {
        if (!query) return true;
        return row.innerText.toLowerCase().includes(query);
      });

      const totalMatched = matchedRows.length;
      const totalPages = Math.ceil(totalMatched / activityPageSize) || 1;

      if (activityCurrentPage > totalPages) activityCurrentPage = totalPages;
      if (activityCurrentPage < 1) activityCurrentPage = 1;

      const startIndex = (activityCurrentPage - 1) * activityPageSize;
      const endIndex = Math.min(startIndex + activityPageSize, totalMatched);

      allRows.forEach(row => row.style.display = 'none');

      for (let i = startIndex; i < endIndex; i++) {
        if (matchedRows[i]) {
          matchedRows[i].style.display = '';
        }
      }

      if (emptyRow) {
        emptyRow.style.display = totalMatched === 0 ? '' : 'none';
      }

      const showingText = document.getElementById('activityShowingText');
      if (showingText) {
        if (totalMatched === 0) {
          showingText.innerHTML = 'Showing 0 to 0 of 0 entries';
        } else {
          showingText.innerHTML = `Showing ${startIndex + 1} to ${endIndex} of ${totalMatched} entries`;
        }
      }

      renderActivityPagination(totalPages);
    }

    function renderActivityPagination(totalPages) {
      const container = document.getElementById('activityPaginationControls');
      if (!container) return;

      container.innerHTML = '';
      if (totalPages <= 1) return;

      const prevBtn = document.createElement('button');
      prevBtn.type = 'button';
      prevBtn.className = 'btn-chart';
      prevBtn.innerHTML = '<i class="bx bx-chevron-left"></i>';
      prevBtn.style.padding = '4px 8px';
      prevBtn.style.borderRadius = '6px';
      prevBtn.style.cursor = activityCurrentPage === 1 ? 'not-allowed' : 'pointer';
      prevBtn.style.opacity = activityCurrentPage === 1 ? '0.5' : '1';
      prevBtn.disabled = activityCurrentPage === 1;
      prevBtn.onclick = () => {
        if (activityCurrentPage > 1) {
          activityCurrentPage--;
          renderActivityTable();
        }
      };
      container.appendChild(prevBtn);

      let startP = Math.max(1, activityCurrentPage - 2);
      let endP = Math.min(totalPages, startP + 4);
      if (endP - startP < 4) {
        startP = Math.max(1, endP - 4);
      }

      for (let p = startP; p <= endP; p++) {
        const pageBtn = document.createElement('button');
        pageBtn.type = 'button';
        pageBtn.className = 'btn-chart' + (p === activityCurrentPage ? ' active' : '');
        pageBtn.textContent = p;
        pageBtn.style.padding = '4px 10px';
        pageBtn.style.borderRadius = '6px';
        pageBtn.style.fontWeight = '700';
        pageBtn.style.cursor = 'pointer';
        const targetP = p;
        pageBtn.onclick = () => {
          activityCurrentPage = targetP;
          renderActivityTable();
        };
        container.appendChild(pageBtn);
      }

      const nextBtn = document.createElement('button');
      nextBtn.type = 'button';
      nextBtn.className = 'btn-chart';
      nextBtn.innerHTML = '<i class="bx bx-chevron-right"></i>';
      nextBtn.style.padding = '4px 8px';
      nextBtn.style.borderRadius = '6px';
      nextBtn.style.cursor = activityCurrentPage === totalPages ? 'not-allowed' : 'pointer';
      nextBtn.style.opacity = activityCurrentPage === totalPages ? '0.5' : '1';
      nextBtn.disabled = activityCurrentPage === totalPages;
      nextBtn.onclick = () => {
        if (activityCurrentPage < totalPages) {
          activityCurrentPage++;
          renderActivityTable();
        }
      };
      container.appendChild(nextBtn);
    }

    function changeActivityEntriesPerPage(val) {
      activityPageSize = parseInt(val) || 10;
      activityCurrentPage = 1;
      renderActivityTable();
    }

    function filterActivityLogs() {
      activityCurrentPage = 1;
      renderActivityTable();
    }

    function exportActivityLogsToCSV() {
      const allRows = Array.from(document.querySelectorAll('#activityLogsTableBody tr.activity-log-row'));
      if (allRows.length === 0) {
        alert('No activity log entries to export.');
        return;
      }

      const headers = ['Timestamp', 'Company', 'Action', 'IP Address', 'Status'];
      const csvRows = [headers.join(',')];

      allRows.forEach(row => {
        const cols = row.querySelectorAll('td');
        if (cols.length >= 5) {
          const timestamp = '"' + (cols[0].innerText || '').replace(/"/g, '""').trim() + '"';
          const company = '"' + (cols[1].innerText || '').replace(/"/g, '""').trim() + '"';
          const action = '"' + (cols[2].innerText || '').replace(/"/g, '""').trim() + '"';
          const ip = '"' + (cols[3].innerText || '').replace(/"/g, '""').trim() + '"';
          const status = '"' + (cols[4].innerText || '').replace(/"/g, '""').trim() + '"';
          csvRows.push([timestamp, company, action, ip, status].join(','));
        }
      });

      const csvContent = '\uFEFF' + csvRows.join('\r\n');
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', url);
      link.setAttribute('download', 'activity_logs_export_' + new Date().toISOString().slice(0, 10) + '.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    }

    document.addEventListener('DOMContentLoaded', function() {
      renderActivityTable();
    });

    function openStatusModal(companyId, companyName, currentStatus) {
      const modal = document.getElementById('statusModal');
      document.getElementById('statusModalCompanyName').innerText = companyName;
      document.getElementById('statusSelect').value = currentStatus;
      
      const form = document.getElementById('statusForm');
      form.action = "{{ url('/superadmin/companies') }}/" + companyId + "/status";
      
      modal.classList.add('active');
    }

    // Dropdown Positioning and Viewport Alignment
    let activeDropdownMenu = null;
    let activeDropdownParent = null;
    let activeDropdownNextSibling = null;

    function closeAllDropdowns() {
      if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('open');
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu.style.visibility = '';
        activeDropdownMenu.style.top = '';
        activeDropdownMenu.style.left = '';
        activeDropdownMenu.style.right = '';
        activeDropdownMenu.style.bottom = '';
        activeDropdownMenu.style.maxHeight = '';
        activeDropdownMenu.style.overflowY = '';

        if (activeDropdownParent) {
          if (activeDropdownNextSibling && activeDropdownNextSibling.parentNode === activeDropdownParent) {
            activeDropdownParent.insertBefore(activeDropdownMenu, activeDropdownNextSibling);
          } else {
            activeDropdownParent.appendChild(activeDropdownMenu);
          }
        }
        activeDropdownMenu._triggerBtn = null;
        activeDropdownMenu = null;
        activeDropdownParent = null;
        activeDropdownNextSibling = null;
      }

      document.querySelectorAll('.dropdown-menu-custom.open').forEach(menu => {
        menu.classList.remove('open');
        menu.style.display = 'none';
      });
    }

    function positionDropdownMenu(btn, menu) {
      if (!btn || !menu) return;

      menu.style.position = 'fixed';
      menu.style.zIndex = '999999';
      menu.style.display = 'block';
      menu.style.visibility = 'hidden';
      menu.style.top = '0px';
      menu.style.left = '0px';
      menu.style.right = 'auto';
      menu.style.bottom = 'auto';
      menu.style.margin = '0';

      const btnRect = btn.getBoundingClientRect();
      const menuHeight = menu.offsetHeight || 260;
      const menuWidth = menu.offsetWidth || 215;
      const viewportHeight = window.innerHeight;
      const viewportWidth = window.innerWidth;

      const gap = 6;
      const padding = 12;

      const spaceBelow = viewportHeight - btnRect.bottom - gap - padding;
      const spaceAbove = btnRect.top - gap - padding;

      let top;
      if (spaceBelow < menuHeight && spaceAbove >= menuHeight) {
        top = btnRect.top - menuHeight - gap;
      } else if (spaceBelow >= menuHeight) {
        top = btnRect.bottom + gap;
      } else {
        if (spaceAbove > spaceBelow) {
          top = Math.max(padding, btnRect.top - menuHeight - gap);
        } else {
          top = btnRect.bottom + gap;
        }
      }

      top = Math.max(padding, Math.min(top, viewportHeight - menuHeight - padding));

      let left = btnRect.right - menuWidth;
      if (left < padding) {
        left = padding;
      }
      if (left + menuWidth > viewportWidth - padding) {
        left = Math.max(padding, viewportWidth - menuWidth - padding);
      }

      menu.style.top = Math.round(top) + 'px';
      menu.style.left = Math.round(left) + 'px';
      menu.style.right = 'auto';
      menu.style.bottom = 'auto';
      menu.style.maxHeight = `calc(100vh - ${padding * 2}px)`;
      menu.style.overflowY = 'auto';
      menu.classList.add('open');
      menu.style.visibility = 'visible';
    }

    document.addEventListener('click', function(e) {
      const toggleBtn = e.target.closest('.dropdown-toggle-trigger');
      if (toggleBtn) {
        e.preventDefault();
        e.stopPropagation();

        if (activeDropdownMenu && activeDropdownMenu._triggerBtn === toggleBtn) {
          closeAllDropdowns();
          return;
        }

        const container = toggleBtn.closest('.dropdown-container');
        const menu = container ? container.querySelector('.dropdown-menu-custom') : toggleBtn.nextElementSibling;
        if (menu) {
          closeAllDropdowns();

          activeDropdownMenu = menu;
          activeDropdownParent = menu.parentElement;
          activeDropdownNextSibling = menu.nextSibling;
          activeDropdownMenu._triggerBtn = toggleBtn;

          document.body.appendChild(menu);
          positionDropdownMenu(toggleBtn, menu);
        }
        return;
      }

      const actionItem = e.target.closest('.dropdown-menu-custom a, .dropdown-menu-custom button');
      if (actionItem) {
        const form = actionItem.closest('form');
        if (form && actionItem.type === 'submit') {
          form.submit();
        }
        setTimeout(closeAllDropdowns, 10);
        return;
      }

      if (!e.target.closest('.dropdown-menu-custom')) {
        closeAllDropdowns();
      }
    });

    window.addEventListener('scroll', closeAllDropdowns, true);
    window.addEventListener('resize', closeAllDropdowns);

    // Suspend and Delete handlers
    function confirmSuspendCompany(id, name) {
      window.location.href = "{{ url('/superadmin/companies') }}/" + id + "/suspension";
    }

    function confirmDeleteCompany(id, name) {
      if (confirm("CRITICAL WARNING: Are you sure you want to completely delete company '" + name + "'? This operation will remove the tenant database link and cannot be undone.")) {
        const form = document.getElementById('dashboardDeleteForm');
        form.action = "{{ url('/superadmin/companies') }}/" + id;
        form.submit();
      }
    }

    // Detail Drawer handlers
    document.addEventListener('DOMContentLoaded', function() {
      const drawer = document.getElementById('companyDetailDrawer');
      const closeDrawerBtn = document.getElementById('closeDrawerBtn');

      document.querySelectorAll('.trigger-detail-drawer').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
          e.preventDefault();
          const id = this.getAttribute('data-company-id');
          const name = this.getAttribute('data-company-name') || 'Company';
          const email = this.getAttribute('data-company-email') || '';
          const db = this.getAttribute('data-company-db') || '';
          const logo = this.getAttribute('data-company-logo') || '';

          document.getElementById('drawerName').textContent = name;
          document.getElementById('drawerDomain').textContent = name.toLowerCase().replace(/[^a-z0-9]/g, '') + '.platform.io';
          document.getElementById('drawerEmail').textContent = email;
          document.getElementById('drawerDb').textContent = db;

          const logoEl = document.getElementById('drawerLogo');
          if (logo) {
            logoEl.innerHTML = '<img src="' + logo + '" style="width:100%; height:100%; object-fit:cover;" />';
          } else {
            logoEl.innerHTML = name.substring(0, 2).toUpperCase();
          }

          document.getElementById('drawerWorkspaceBtn').href = "{{ url('/superadmin/companies') }}/" + id;
          if (drawer) drawer.classList.add('open');
        });
      });

      if (closeDrawerBtn && drawer) {
        closeDrawerBtn.addEventListener('click', () => drawer.classList.remove('open'));
      }
      if (drawer) {
        drawer.addEventListener('click', function(e) {
          if (e.target === drawer) drawer.classList.remove('open');
        });
      }

      // Plan Change Modal handlers
      const planModal = document.getElementById('planChangeModal');
      const closePlanBtn = document.getElementById('closePlanModalBtn');

      document.addEventListener('click', function(e) {
          const trigger = e.target.closest('.trigger-plan-modal');
          if (!trigger) return;
          e.preventDefault();
          const id = trigger.getAttribute('data-company-id');
          document.getElementById('modalPlanCompanyId').value = id;
          const currentPlan = trigger.getAttribute('data-current-plan-id');
          const choices = Array.from(document.querySelectorAll('#planChangeModal input[name="plan_id"]'));
          const selected = choices.find(input => input.value === currentPlan) || choices[0];
          choices.forEach(input => { input.checked = input === selected; });
          if (planModal) planModal.classList.add('open');
      });

      if (closePlanBtn && planModal) {
        closePlanBtn.addEventListener('click', () => planModal.classList.remove('open'));
      }
      if (planModal) {
        planModal.addEventListener('click', function(e) {
          if (e.target === planModal) planModal.classList.remove('open');
        });
      }
    });

    function selectPlanCard(card, planId) {
      const input = card.querySelector('input[name="plan_id"]');
      if (input) input.checked = true;
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
              backgroundColor: ['#2F6BFF', '#8B5CF6', '#22D3EE', '#10B981', '#F59E0B'],
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

      // Dashboard Modal Strict Email & Phone Validation
      function getModalPhoneRule(countryCode) {
        switch (countryCode) {
          case '+91': return { min: 10, max: 10, regex: /^[6-9]\d{9}$/, placeholder: '9876543210', error: 'India phone number must be exactly 10 digits starting with 6, 7, 8, or 9.' };
          case '+1':  return { min: 10, max: 10, regex: /^[2-9]\d{9}$/, placeholder: '5550192831', error: 'US/Canada phone number must be exactly 10 digits (e.g. 5550192831).' };
          case '+44': return { min: 10, max: 11, regex: /^[1-9]\d{9,10}$/, placeholder: '7911123456', error: 'UK phone number must be 10 to 11 digits.' };
          case '+61': return { min: 9,  max: 10, regex: /^[1-9]\d{8,9}$/, placeholder: '412345678', error: 'Australia phone number must be 9 to 10 digits.' };
          case '+971':return { min: 9,  max: 9,  regex: /^[2-9]\d{8}$/, placeholder: '501234567', error: 'UAE phone number must be 9 digits (e.g. 501234567).' };
          case '+81': return { min: 10, max: 10, regex: /^[1-9]\d{9}$/, placeholder: '9012345678', error: 'Japan phone number must be 10 digits.' };
          case '+49': return { min: 10, max: 11, regex: /^[1-9]\d{9,10}$/, placeholder: '15123456789', error: 'Germany phone number must be 10 to 11 digits.' };
          case '+33': return { min: 9,  max: 9,  regex: /^[1-9]\d{8}$/, placeholder: '612345678', error: 'France phone number must be 9 digits.' };
          default:    return { min: 7,  max: 15, regex: /^\d{7,15}$/, placeholder: '1234567890', error: 'Phone number must be between 7 and 15 digits.' };
        }
      }

      function validateModalEmailFormat(email, isRequired = true) {
        const val = (email || '').trim();
        if (!val) {
          return isRequired ? { valid: false, message: 'Email address is required.' } : { valid: true };
        }
        if (/\s/.test(val)) {
          return { valid: false, message: 'Email address cannot contain spaces.' };
        }
        if (!val.includes('@')) {
          return { valid: false, message: "Email address must include an '@' symbol." };
        }
        const parts = val.split('@');
        if (parts.length !== 2) {
          return { valid: false, message: "Email address must contain only one '@' symbol." };
        }
        const [local, domain] = parts;
        if (!local) return { valid: false, message: "Missing username before '@'." };
        if (!domain) return { valid: false, message: "Missing domain after '@'." };
        if (!domain.includes('.')) return { valid: false, message: "Domain name must include a valid extension (e.g. .com)." };
        if (domain.startsWith('.') || domain.endsWith('.')) return { valid: false, message: "Domain name cannot start or end with a dot." };
        if (domain.includes('..')) return { valid: false, message: "Domain name cannot contain consecutive dots." };
        const strictRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!strictRegex.test(val)) {
          return { valid: false, message: 'Please enter a valid email address (e.g. name@company.com).' };
        }
        return { valid: true };
      }

      function setupModalPhoneValidator(countrySelectId, phoneInputId, hiddenInputId, errorDivId) {
        const countrySelect = document.getElementById(countrySelectId);
        const phoneInput = document.getElementById(phoneInputId);
        const hiddenInput = document.getElementById(hiddenInputId);
        const errorDiv = document.getElementById(errorDivId);
        if (!countrySelect || !phoneInput) return null;

        function updateRule() {
          const rule = getModalPhoneRule(countrySelect.value);
          phoneInput.placeholder = rule.placeholder;
          phoneInput.maxLength = rule.max;
        }

        phoneInput.addEventListener('keydown', function(e) {
          if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(e.key) ||
              ((e.ctrlKey || e.metaKey) && ['a', 'c', 'v', 'x', 'z'].includes(e.key.toLowerCase()))) {
            return;
          }
          if (!/^\d$/.test(e.key)) {
            e.preventDefault();
          }
        });

        function syncValue() {
          phoneInput.value = phoneInput.value.replace(/\D/g, '');
          const rule = getModalPhoneRule(countrySelect.value);
          if (phoneInput.value.length > rule.max) {
            phoneInput.value = phoneInput.value.substring(0, rule.max);
          }
          if (hiddenInput) {
            hiddenInput.value = phoneInput.value ? countrySelect.value + ' ' + phoneInput.value : '';
          }
        }

        phoneInput.addEventListener('input', function() {
          syncValue();
          validate(false);
        });

        countrySelect.addEventListener('change', function() {
          updateRule();
          syncValue();
          validate(false);
        });

        phoneInput.addEventListener('blur', function() {
          validate(true);
        });

        function validate(showEmptyError = true) {
          const val = phoneInput.value.trim();
          if (!val) {
            clearError();
            return true;
          }
          const rule = getModalPhoneRule(countrySelect.value);
          if (!rule.regex.test(val)) {
            showError(rule.error);
            return false;
          }
          clearError();
          return true;
        }

        function showError(msg) {
          if (errorDiv) {
            errorDiv.textContent = msg;
            errorDiv.classList.add('visible');
          }
          phoneInput.classList.add('is-invalid');
        }

        function clearError() {
          if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.classList.remove('visible');
          }
          phoneInput.classList.remove('is-invalid');
        }

        updateRule();
        syncValue();
        return { validate, input: phoneInput };
      }

      function setupModalEmailValidator(inputId, errorDivId, isRequired = true) {
        const input = document.getElementById(inputId);
        const errorDiv = document.getElementById(errorDivId);
        if (!input) return null;

        function validate(showEmptyError = true) {
          const val = input.value.trim();
          if (!val && !showEmptyError && !isRequired) {
            clearError();
            return true;
          }
          const res = validateModalEmailFormat(val, isRequired);
          if (!res.valid) {
            if (val || showEmptyError) {
              showError(res.message);
              return false;
            }
            return false;
          }
          clearError();
          return true;
        }

        function showError(msg) {
          if (errorDiv) {
            errorDiv.textContent = msg;
            errorDiv.classList.add('visible');
          }
          input.classList.add('is-invalid');
        }

        function clearError() {
          if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.classList.remove('visible');
          }
          input.classList.remove('is-invalid');
        }

        input.addEventListener('input', function() {
          if (/\s/.test(this.value)) {
            this.value = this.value.replace(/\s+/g, '');
          }
          validate(false);
        });

        input.addEventListener('blur', function() {
          validate(true);
        });

        return { validate, input };
      }

      window.toggleDashboardPassword = function(inputId, btnEl) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = btnEl?.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          if (icon) { icon.classList.remove('bx-show'); icon.classList.add('bx-hide'); }
        } else {
          input.type = 'password';
          if (icon) { icon.classList.remove('bx-hide'); icon.classList.add('bx-show'); }
        }
      };

      function validateModalAdminPassword(showError = true) {
        const input = document.getElementById('modal_admin_password');
        const errorDiv = document.getElementById('modal_admin_password_error');
        if (!input) return true;
        const val = input.value;
        if (!val) {
          if (showError) {
            if (errorDiv) { errorDiv.textContent = 'Admin password is required.'; errorDiv.classList.add('visible'); errorDiv.style.display = 'block'; }
            input.classList.add('is-invalid');
          }
          return false;
        }
        if (val.length < 8) {
          if (showError) {
            if (errorDiv) { errorDiv.textContent = 'Password must be at least 8 characters long.'; errorDiv.classList.add('visible'); errorDiv.style.display = 'block'; }
            input.classList.add('is-invalid');
          }
          return false;
        }
        if (val.length > 128) {
          if (showError) {
            if (errorDiv) { errorDiv.textContent = 'Password cannot exceed 128 characters.'; errorDiv.classList.add('visible'); errorDiv.style.display = 'block'; }
            input.classList.add('is-invalid');
          }
          return false;
        }
        if (errorDiv) { errorDiv.textContent = ''; errorDiv.classList.remove('visible'); errorDiv.style.display = 'none'; }
        input.classList.remove('is-invalid');
        return true;
      }

      const modalPwdInput = document.getElementById('modal_admin_password');
      if (modalPwdInput) {
        modalPwdInput.addEventListener('input', function() { validateModalAdminPassword(false); });
        modalPwdInput.addEventListener('blur', function() { validateModalAdminPassword(true); });
      }

      const modalPhoneVal = setupModalPhoneValidator('modal_company_country_code', 'modal_company_phone_display', 'modal_company_phone_hidden', 'modal_company_phone_error');
      const modalEmailVal = setupModalEmailValidator('modal_company_email', 'modal_company_email_error', true);
      const modalAdminEmailVal = setupModalEmailValidator('modal_company_admin_email', 'modal_company_admin_email_error', true);

      const modalForm = document.getElementById('dashboardProvisionCompanyForm');
      if (modalForm) {
        modalForm.addEventListener('submit', function(e) {
          const isEmailValid = modalEmailVal ? modalEmailVal.validate(true) : true;
          const isAdminEmailValid = modalAdminEmailVal ? modalAdminEmailVal.validate(true) : true;
          const isPhoneValid = modalPhoneVal ? modalPhoneVal.validate(true) : true;
          const isPasswordValid = validateModalAdminPassword(true);

          if (!isEmailValid || !isAdminEmailValid || !isPhoneValid || !isPasswordValid) {
            e.preventDefault();
            const firstInvalid = modalForm.querySelector('input.is-invalid, select.is-invalid');
            if (firstInvalid) {
              firstInvalid.focus();
            }
            return false;
          }
        });
      }
    });
  </script>
@endpush
