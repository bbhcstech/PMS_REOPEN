@extends('layouts.superadmin')

@section('title', 'Complaints & Support Center')

@push('styles')
<style>
  /* ==========================================================================
     COMPLAINTS & SUPPORT CENTER — DUAL THEME (LIGHT & DARK)
     ========================================================================== */
  :root,
  html[data-pms-theme="light"],
  html[data-theme="light"] {
    --cmp-bg-card: #ffffff;
    --cmp-bg-subtle: #f8fafc;
    --cmp-bg-input: #ffffff;
    --cmp-border: #e2e8f0;
    --cmp-border-strong: #cbd5e1;
    --cmp-text-main: #0f172a;
    --cmp-text-body: #334155;
    --cmp-text-muted: #64748b;
    --cmp-table-hover: rgba(248, 250, 252, 0.95);
    --cmp-card-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    --cmp-drawer-bg: #ffffff;
    --cmp-timeline-client: #f1f5f9;
    --cmp-timeline-client-text: #0f172a;
    --cmp-timeline-admin: #eff6ff;
    --cmp-timeline-admin-text: #1e3a8a;
    --cmp-timeline-admin-border: #bfdbfe;
  }

  html[data-pms-theme="dark"],
  html[data-theme="dark"],
  html[data-bs-theme="dark"] {
    --cmp-bg-card: #0F1530;
    --cmp-bg-subtle: #141B3D;
    --cmp-bg-input: #141B3D;
    --cmp-border: rgba(238, 241, 251, 0.09);
    --cmp-border-strong: rgba(238, 241, 251, 0.16);
    --cmp-text-main: #EEF1FB;
    --cmp-text-body: #CBD5E1;
    --cmp-text-muted: #9AA3C7;
    --cmp-table-hover: rgba(20, 27, 61, 0.65);
    --cmp-card-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
    --cmp-drawer-bg: #0F1530;
    --cmp-timeline-client: #141B3D;
    --cmp-timeline-client-text: #EEF1FB;
    --cmp-timeline-admin: rgba(47, 107, 255, 0.16);
    --cmp-timeline-admin-text: #93C5FD;
    --cmp-timeline-admin-border: rgba(47, 107, 255, 0.35);
  }

  .complaints-wrapper {
    padding: 10px 0 40px;
  }

  /* ---- HEADER GRADIENT BANNER ---- */
  .page-header-card {
    background: linear-gradient(135deg, #071f48 0%, #1e40af 38%, #0284c7 74%, #0d9488 100%);
    border-radius: 16px;
    padding: 26px 32px;
    color: #ffffff;
    margin-bottom: 22px;
    box-shadow: 0 14px 34px -4px rgba(30, 64, 175, 0.28);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    position: relative;
    overflow: hidden;
  }

  .page-header-card::after {
    content: '';
    position: absolute;
    right: -40px;
    top: -40px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(34, 211, 238, 0.2) 0%, transparent 70%);
    pointer-events: none;
  }

  .page-header-card h1 {
    font-size: 26px;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 6px 0;
    letter-spacing: -0.4px;
    line-height: 1.2;
  }

  .page-header-card p {
    font-size: 13.5px;
    color: rgba(255, 255, 255, 0.92);
    margin: 0;
    font-weight: 500;
  }

  .live-connection-badge {
    background: rgba(255, 255, 255, 0.16);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 9999px;
    padding: 7px 16px;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    white-space: nowrap;
  }

  /* ---- TOP KPI STAT CARDS (6 IN A ROW) ---- */
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px;
    margin-bottom: 22px;
  }

  @media (max-width: 1320px) {
    .kpi-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (max-width: 680px) {
    .kpi-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
    }
  }

  @media (max-width: 420px) {
    .kpi-grid {
      grid-template-columns: 1fr;
    }
  }

  .kpi-card {
    background: var(--cmp-bg-card);
    border: 1px solid var(--cmp-border);
    border-radius: 16px;
    padding: 18px 18px;
    box-shadow: var(--cmp-card-shadow);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 14px;
  }

  .kpi-card:hover {
    transform: translateY(-2px);
    border-color: #2F6BFF;
    box-shadow: 0 10px 24px rgba(47, 107, 255, 0.12);
  }

  .kpi-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
  }

  .kpi-icon.icon-total      { background: #eff6ff; color: #2563eb; }
  .kpi-icon.icon-open       { background: #e0f2fe; color: #0284c7; }
  .kpi-icon.icon-inprogress { background: #fef3c7; color: #d97706; }
  .kpi-icon.icon-resolved   { background: #eff6ff; color: #2F6BFF; }
  .kpi-icon.icon-critical   { background: #ffe4e6; color: #e11d48; }
  .kpi-icon.icon-unassigned { background: #f3e8ff; color: #9333ea; }

  html[data-pms-theme="dark"] .kpi-icon.icon-total,
  html[data-theme="dark"] .kpi-icon.icon-total,
  html[data-bs-theme="dark"] .kpi-icon.icon-total {
    background: rgba(37, 99, 235, 0.18); color: #60a5fa;
  }
  html[data-pms-theme="dark"] .kpi-icon.icon-open,
  html[data-theme="dark"] .kpi-icon.icon-open,
  html[data-bs-theme="dark"] .kpi-icon.icon-open {
    background: rgba(2, 132, 199, 0.18); color: #38bdf8;
  }
  html[data-pms-theme="dark"] .kpi-icon.icon-inprogress,
  html[data-theme="dark"] .kpi-icon.icon-inprogress,
  html[data-bs-theme="dark"] .kpi-icon.icon-inprogress {
    background: rgba(217, 119, 6, 0.18); color: #fbbf24;
  }
  html[data-pms-theme="dark"] .kpi-icon.icon-resolved,
  html[data-theme="dark"] .kpi-icon.icon-resolved,
  html[data-bs-theme="dark"] .kpi-icon.icon-resolved {
    background: rgba(47, 107, 255, 0.18); color: #818cf8;
  }
  html[data-pms-theme="dark"] .kpi-icon.icon-critical,
  html[data-theme="dark"] .kpi-icon.icon-critical,
  html[data-bs-theme="dark"] .kpi-icon.icon-critical {
    background: rgba(225, 29, 72, 0.18); color: #f43f5e;
  }
  html[data-pms-theme="dark"] .kpi-icon.icon-unassigned,
  html[data-theme="dark"] .kpi-icon.icon-unassigned,
  html[data-bs-theme="dark"] .kpi-icon.icon-unassigned {
    background: rgba(147, 51, 234, 0.18); color: #c084fc;
  }

  .kpi-val {
    font-size: 26px;
    font-weight: 800;
    line-height: 1;
    color: var(--cmp-text-main);
    margin-bottom: 4px;
    letter-spacing: -0.5px;
  }

  .kpi-label {
    font-size: 11px;
    color: var(--cmp-text-muted);
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
  }

  /* ---- FILTER CARD & CONTROLS ---- */
  .filter-card {
    background: var(--cmp-bg-card);
    border: 1px solid var(--cmp-border);
    border-radius: 16px;
    padding: 18px 22px;
    margin-bottom: 22px;
    box-shadow: var(--cmp-card-shadow);
  }

  .filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
  }

  .form-control-custom {
    background: var(--cmp-bg-input);
    border: 1px solid var(--cmp-border-strong);
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    color: var(--cmp-text-main);
    font-weight: 600;
    outline: none;
    transition: all 0.15s ease;
  }

  .form-control-custom:focus {
    border-color: #2F6BFF;
    box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.2);
  }

  .btn-filter-solid {
    background: #0f2744;
    color: #ffffff;
    padding: 8px 18px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .btn-filter-solid:hover {
    background: #1e40af;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
  }

  html[data-pms-theme="dark"] .btn-filter-solid,
  html[data-theme="dark"] .btn-filter-solid,
  html[data-bs-theme="dark"] .btn-filter-solid {
    background: #2F6BFF;
  }
  html[data-pms-theme="dark"] .btn-filter-solid:hover,
  html[data-theme="dark"] .btn-filter-solid:hover,
  html[data-bs-theme="dark"] .btn-filter-solid:hover {
    background: #1e4fcc;
  }

  .btn-export-outline {
    background: transparent;
    border: 1.5px solid #2F6BFF;
    color: #2F6BFF;
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .btn-export-outline:hover {
    background: rgba(47, 107, 255, 0.08);
  }

  .export-dropdown-menu {
    background: var(--cmp-bg-card);
    border: 1px solid var(--cmp-border-strong);
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    z-index: 100;
    overflow: hidden;
  }

  .export-dropdown-menu a {
    color: var(--cmp-text-body);
    transition: background 0.15s ease;
  }

  .export-dropdown-menu a:hover {
    background: var(--cmp-bg-subtle);
    color: var(--cmp-text-main);
  }

  /* ---- TABLE CARD & DATA TABLE ---- */
  .table-card {
    background: var(--cmp-bg-card);
    border: 1px solid var(--cmp-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--cmp-card-shadow);
  }

  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-bottom: 2px solid var(--cmp-border);
  }

  /* Exact vivid blue horizontal scrollbar from screenshot */
  .table-responsive::-webkit-scrollbar {
    height: 7px;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: var(--cmp-border);
    border-radius: 4px;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: #2F6BFF;
    border-radius: 4px;
  }

  .table-responsive::-webkit-scrollbar-thumb:hover {
    background: #1e4fcc;
  }

  .custom-table {
    width: 100%;
    min-width: 1200px;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
    border: 1px solid var(--cmp-border);
  }

  .custom-table th {
    background: var(--cmp-bg-subtle);
    padding: 14px 16px;
    font-weight: 800;
    color: var(--cmp-text-muted);
    border-bottom: 1.5px solid var(--cmp-border);
    border-right: 1px solid var(--cmp-border);
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.6px;
  }

  .custom-table th:last-child {
    border-right: none;
  }

  .custom-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--cmp-border);
    border-right: 1px solid var(--cmp-border);
    color: var(--cmp-text-main);
    vertical-align: middle;
  }

  .custom-table td:last-child {
    border-right: none;
  }

  .custom-table tbody tr:hover {
    background: var(--cmp-table-hover);
  }

  /* Status Badges */
  .badge-status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    white-space: nowrap;
    letter-spacing: 0.3px;
  }

  .status-OPEN {
    background: #eff6ff; color: #1d4ed8; border: 1px solid #93c5fd;
  }
  .status-IN_PROGRESS {
    background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;
  }
  .status-WAITING_FOR_COMPANY {
    background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe;
  }
  .status-RESOLVED {
    background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;
  }
  .status-CLOSED {
    background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;
  }
  .status-REOPENED {
    background: #fff1f2; color: #be123c; border: 1px solid #fda4af;
  }

  html[data-pms-theme="dark"] .status-OPEN,
  html[data-theme="dark"] .status-OPEN,
  html[data-bs-theme="dark"] .status-OPEN {
    background: rgba(29, 78, 216, 0.2); color: #93c5fd; border-color: rgba(147, 197, 253, 0.35);
  }
  html[data-pms-theme="dark"] .status-IN_PROGRESS,
  html[data-theme="dark"] .status-IN_PROGRESS,
  html[data-bs-theme="dark"] .status-IN_PROGRESS {
    background: rgba(217, 119, 6, 0.2); color: #fbbf24; border-color: rgba(251, 191, 36, 0.4);
  }
  html[data-pms-theme="dark"] .status-WAITING_FOR_COMPANY,
  html[data-theme="dark"] .status-WAITING_FOR_COMPANY,
  html[data-bs-theme="dark"] .status-WAITING_FOR_COMPANY {
    background: rgba(107, 33, 168, 0.22); color: #d8b4fe; border-color: rgba(216, 180, 254, 0.35);
  }
  html[data-pms-theme="dark"] .status-RESOLVED,
  html[data-theme="dark"] .status-RESOLVED,
  html[data-bs-theme="dark"] .status-RESOLVED {
    background: rgba(4, 120, 87, 0.2); color: #6ee7b7; border-color: rgba(110, 231, 183, 0.35);
  }
  html[data-pms-theme="dark"] .status-CLOSED,
  html[data-theme="dark"] .status-CLOSED,
  html[data-bs-theme="dark"] .status-CLOSED {
    background: rgba(51, 65, 85, 0.3); color: #cbd5e1; border-color: rgba(203, 213, 225, 0.2);
  }
  html[data-pms-theme="dark"] .status-REOPENED,
  html[data-theme="dark"] .status-REOPENED,
  html[data-bs-theme="dark"] .status-REOPENED {
    background: rgba(190, 18, 60, 0.22); color: #fda4af; border-color: rgba(253, 164, 175, 0.4);
  }

  /* Priority Badges */
  .badge-priority {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
    display: inline-block;
    text-transform: uppercase;
  }

  .prio-LOW      { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
  .prio-MEDIUM   { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
  .prio-HIGH     { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
  .prio-CRITICAL { background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }

  html[data-pms-theme="dark"] .prio-LOW,
  html[data-theme="dark"] .prio-LOW,
  html[data-bs-theme="dark"] .prio-LOW {
    background: rgba(255,255,255,0.06); color: #cbd5e1; border-color: rgba(255,255,255,0.12);
  }
  html[data-pms-theme="dark"] .prio-MEDIUM,
  html[data-theme="dark"] .prio-MEDIUM,
  html[data-bs-theme="dark"] .prio-MEDIUM {
    background: rgba(2, 132, 199, 0.2); color: #38bdf8; border-color: rgba(2, 132, 199, 0.35);
  }
  html[data-pms-theme="dark"] .prio-HIGH,
  html[data-theme="dark"] .prio-HIGH,
  html[data-bs-theme="dark"] .prio-HIGH {
    background: rgba(234, 88, 12, 0.2); color: #fdba74; border-color: rgba(234, 88, 12, 0.35);
  }
  html[data-pms-theme="dark"] .prio-CRITICAL,
  html[data-theme="dark"] .prio-CRITICAL,
  html[data-bs-theme="dark"] .prio-CRITICAL {
    background: rgba(225, 29, 72, 0.2); color: #f43f5e; border-color: rgba(225, 29, 72, 0.4);
  }

  /* Custom Checkbox */
  .custom-checkbox {
    width: 17px;
    height: 17px;
    accent-color: #2F6BFF;
    cursor: pointer;
    border-radius: 4px;
    vertical-align: middle;
  }

  /* Category Badge */
  .badge-category {
    background: var(--cmp-bg-subtle);
    color: var(--cmp-text-body);
    border: 1px solid var(--cmp-border-strong);
    font-weight: 700;
    font-size: 11.5px;
    padding: 5px 10px;
    border-radius: 6px;
    display: inline-block;
  }

  /* Assigned To Badges */
  .badge-assigned {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
    font-weight: 700;
    font-size: 11.5px;
    padding: 6px 12px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
  }

  .badge-unassigned {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fca5a5;
    font-weight: 700;
    font-size: 11.5px;
    padding: 6px 12px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
  }

  html[data-pms-theme="dark"] .badge-assigned,
  html[data-theme="dark"] .badge-assigned,
  html[data-bs-theme="dark"] .badge-assigned {
    background: rgba(16, 185, 129, 0.18);
    color: #34d399;
    border-color: rgba(16, 185, 129, 0.35);
  }

  html[data-pms-theme="dark"] .badge-unassigned,
  html[data-theme="dark"] .badge-unassigned,
  html[data-bs-theme="dark"] .badge-unassigned {
    background: rgba(239, 68, 68, 0.18);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.35);
  }

  /* Action View Button */
  .btn-action-view {
    background: linear-gradient(135deg, #1e40af 0%, #2F6BFF 100%);
    color: #ffffff;
    padding: 6px 14px;
    font-size: 12.5px;
    font-weight: 700;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
  }

  .btn-action-view:hover {
    box-shadow: 0 4px 12px rgba(47, 107, 255, 0.3);
    color: #ffffff;
    transform: translateY(-1px);
  }

  /* ---- SLIDE DRAWER ---- */
  .drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
  }

  .drawer-overlay.active {
    opacity: 1;
    pointer-events: auto;
  }

  .slide-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: 700px;
    max-width: 95vw;
    height: 100vh;
    background: var(--cmp-drawer-bg);
    z-index: 1000;
    transform: translateX(100%);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: -12px 0 45px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    border-left: 1px solid var(--cmp-border);
  }

  .drawer-overlay.active .slide-drawer {
    transform: translateX(0);
  }

  .drawer-header {
    padding: 16px 24px;
    border-bottom: 1px solid var(--cmp-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--cmp-bg-subtle);
    gap: 12px;
  }

  .drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
  }

  /* Drawer Inner Components */
  .drawer-panel {
    background: var(--cmp-bg-subtle);
    border: 1px solid var(--cmp-border);
    border-radius: 12px;
    padding: 16px;
  }

  .drawer-panel-white {
    background: var(--cmp-bg-card);
    border: 1px solid var(--cmp-border);
    border-radius: 12px;
    padding: 16px;
  }

  .timeline-feed {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin: 20px 0;
  }

  .timeline-item {
    display: flex;
    gap: 12px;
  }

  .timeline-item.super_admin {
    flex-direction: row-reverse;
  }

  .timeline-bubble {
    max-width: 82%;
    padding: 14px 16px;
    border-radius: 14px;
    font-size: 13px;
    line-height: 1.5;
  }

  .timeline-item.company_admin .timeline-bubble {
    background: var(--cmp-timeline-client);
    color: var(--cmp-timeline-client-text);
    border: 1px solid var(--cmp-border);
    border-bottom-left-radius: 2px;
  }

  .timeline-item.super_admin .timeline-bubble {
    background: var(--cmp-timeline-admin);
    color: var(--cmp-timeline-admin-text);
    border: 1px solid var(--cmp-timeline-admin-border);
    border-bottom-right-radius: 2px;
  }

  .timeline-meta {
    font-size: 11px;
    color: var(--cmp-text-muted);
    margin-bottom: 4px;
    font-weight: 700;
  }
</style>
@endpush

@section('content')
<div class="complaints-wrapper">
  
  <!-- ==================== GRADIENT HEADER BANNER ==================== -->
  <div class="page-header-card">
    <div>
      <h1>Complaints &amp; Support Center</h1>
      <p>Manage company complaints, support requests, technical issues, and service-related tickets across all tenants.</p>
    </div>
    <div>
      <div class="live-connection-badge">
        <i class="bx bx-check-shield" style="font-size: 16px;"></i> Live Database Connection
      </div>
    </div>
  </div>

  <!-- ==================== 6 TOP STATISTIC KPIS ==================== -->
  <div class="kpi-grid">
    <!-- Total Tickets -->
    <div class="kpi-card">
      <div class="kpi-icon icon-total">
        <i class="bx bx-file-blank"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['total']) }}</div>
        <div class="kpi-label">Total Tickets</div>
      </div>
    </div>

    <!-- Open -->
    <div class="kpi-card">
      <div class="kpi-icon icon-open">
        <i class="bx bx-folder"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['open']) }}</div>
        <div class="kpi-label">Open</div>
      </div>
    </div>

    <!-- In Progress -->
    <div class="kpi-card">
      <div class="kpi-icon icon-inprogress">
        <i class="bx bx-time-five"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['in_progress']) }}</div>
        <div class="kpi-label">In Progress</div>
      </div>
    </div>

    <!-- Resolved -->
    <div class="kpi-card">
      <div class="kpi-icon icon-resolved">
        <i class="bx bx-check-circle"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['resolved']) }}</div>
        <div class="kpi-label">Resolved</div>
      </div>
    </div>

    <!-- Critical -->
    <div class="kpi-card">
      <div class="kpi-icon icon-critical">
        <i class="bx bx-error-circle"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['critical']) }}</div>
        <div class="kpi-label">Critical</div>
      </div>
    </div>

    <!-- Unassigned -->
    <div class="kpi-card">
      <div class="kpi-icon icon-unassigned">
        <i class="bx bx-user-x"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['unassigned']) }}</div>
        <div class="kpi-label">Unassigned</div>
      </div>
    </div>
  </div>

  <!-- ==================== SEARCH & FILTERS TOOLBAR ==================== -->
  <div class="filter-card">
    <form method="GET" action="{{ route('superadmin.complaints.index') }}" class="filter-form" id="filterForm">
      
      <!-- Show Entries Selector -->
      <div style="display: flex; align-items: center; gap: 6px;">
        <label style="font-size: 13px; font-weight: 700; color: var(--cmp-text-body); margin: 0;">Show</label>
        <select name="per_page" class="form-control-custom" onchange="this.form.submit()" style="width: auto; font-weight: 700;">
          <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 entries</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 entries</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 entries</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 entries</option>
        </select>
      </div>

      <!-- Search Input -->
      <div style="flex: 1; min-width: 220px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Ticket ID, Company, Admin, Subject..." class="form-control-custom w-100" />
      </div>

      <!-- Status Filter -->
      <div>
        <select name="status" class="form-control-custom" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>OPEN</option>
          <option value="IN PROGRESS" {{ request('status') === 'IN PROGRESS' ? 'selected' : '' }}>IN PROGRESS</option>
          <option value="WAITING FOR COMPANY" {{ request('status') === 'WAITING FOR COMPANY' ? 'selected' : '' }}>WAITING FOR COMPANY</option>
          <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
          <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
          <option value="REOPENED" {{ request('status') === 'REOPENED' ? 'selected' : '' }}>REOPENED</option>
        </select>
      </div>

      <!-- Priority Filter -->
      <div>
        <select name="priority" class="form-control-custom" onchange="this.form.submit()">
          <option value="">All Priorities</option>
          <option value="LOW" {{ request('priority') === 'LOW' ? 'selected' : '' }}>LOW</option>
          <option value="MEDIUM" {{ request('priority') === 'MEDIUM' ? 'selected' : '' }}>MEDIUM</option>
          <option value="HIGH" {{ request('priority') === 'HIGH' ? 'selected' : '' }}>HIGH</option>
          <option value="CRITICAL" {{ request('priority') === 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
        </select>
      </div>

      <!-- Category Filter -->
      <div>
        <select name="category" class="form-control-custom" onchange="this.form.submit()">
          <option value="">All Categories</option>
          @foreach(['Technical Issue', 'Subscription', 'Billing', 'Account', 'Payroll', 'HR', 'Security', 'Performance', 'Feature Request', 'Bug Report', 'Other'] as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>

      <!-- Company Filter -->
      <div>
        <select name="company_id" class="form-control-custom" onchange="this.form.submit()">
          <option value="">All Companies</option>
          @foreach($companies as $comp)
            <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
          @endforeach
        </select>
      </div>

      <!-- Sort Filter -->
      <div>
        <select name="sort" class="form-control-custom" onchange="this.form.submit()">
          <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Sort: Newest</option>
          <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Sort: Oldest</option>
          <option value="priority" {{ request('sort') === 'priority' ? 'selected' : '' }}>Sort: Priority</option>
          <option value="recently_updated" {{ request('sort') === 'recently_updated' ? 'selected' : '' }}>Sort: Recently Updated</option>
        </select>
      </div>

      <!-- Filter & Export Action Buttons -->
      <div style="display: flex; align-items: center; gap: 8px;">
        <button type="submit" class="btn-filter-solid">
          <i class="bx bx-filter"></i> Filter
        </button>

        @if(request()->anyFilled(['search', 'status', 'priority', 'category', 'company_id', 'sort', 'per_page']))
          <a href="{{ route('superadmin.complaints.index') }}" class="btn btn-sm btn-light" style="border: 1px solid var(--cmp-border-strong); border-radius: 8px; padding: 7px 12px; font-weight: 700; font-size: 12px; text-decoration: none; color: var(--cmp-text-body); background: var(--cmp-bg-subtle);">Reset</a>
        @endif

        <!-- Export Dropdown -->
        <div class="dropdown-export-wrapper" style="position: relative; display: inline-block;">
          <button type="button" onclick="toggleExportMenu(event)" class="btn-export-outline">
            <i class="bx bx-export" style="font-size: 16px;"></i> Export <i class="bx bx-chevron-down" style="font-size: 14px;"></i>
          </button>
          <div id="exportMenuDropdown" class="export-dropdown-menu" style="display: none; position: absolute; top: calc(100% + 4px); right: 0; min-width: 150px;">
            <a href="{{ route('superadmin.complaints.export', array_merge(request()->query(), ['export_format' => 'csv'])) }}" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; font-size: 13px; font-weight: 600; text-decoration: none;">
              <i class="bx bx-file" style="color: #10b981; font-size: 18px;"></i> CSV Export
            </a>
            <div style="height: 1px; background: var(--cmp-border);"></div>
            <a href="{{ route('superadmin.complaints.export', array_merge(request()->query(), ['export_format' => 'pdf'])) }}" target="_blank" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; font-size: 13px; font-weight: 600; text-decoration: none;">
              <i class="bx bxs-file-pdf" style="color: #ef4444; font-size: 18px;"></i> PDF Export
            </a>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Bulk Selection Toolbar (Hidden by Default) -->
  <div id="selectionToolbar" style="display: none; background: #0f2744; color: #ffffff; border-radius: 12px; padding: 12px 20px; margin-bottom: 16px; align-items: center; justify-content: space-between; box-shadow: 0 4px 14px rgba(15,39,68,0.3);">
    <div style="font-size: 13.5px; font-weight: 700;">
      <i class="bx bx-check-square me-1"></i> <span id="selectedCount">0</span> ticket(s) selected
    </div>
    <div style="display: flex; gap: 10px;">
      <button type="button" onclick="clearSelection()" class="btn btn-sm btn-outline-light" style="font-weight: 700; font-size: 12px; border-radius: 6px;">
        Clear Selection
      </button>
    </div>
  </div>

  <!-- ==================== COMPLAINT DATA TABLE ==================== -->
  <div class="table-card">
    <div class="table-responsive">
      <table class="custom-table">
        <thead>
          <tr>
            <th style="width: 38px; text-align: center;">
              <input type="checkbox" id="selectAllComplaints" class="custom-checkbox" onchange="toggleSelectAll(this)" title="Select All Tickets" />
            </th>
            <th>TICKET ID</th>
            <th>COMPANY</th>
            <th>RAISED BY</th>
            <th>SUBJECT</th>
            <th>CATEGORY</th>
            <th>PRIORITY</th>
            <th>STATUS</th>
            <th>CREATED AT</th>
            <th>LAST UPDATED</th>
            <th>ASSIGNED TO</th>
            <th style="text-align: right; min-width: 90px;">ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tickets as $ticket)
            @php
              $statusKey = str_replace(' ', '_', $ticket->status);
            @endphp
            <tr>
              <td style="text-align: center;">
                <input type="checkbox" class="ticket-checkbox custom-checkbox" value="{{ $ticket->id }}" onchange="updateSelectedCount()" />
              </td>
              <td>
                <a href="javascript:void(0)" onclick="openComplaintDrawer({{ $ticket->id }})" style="font-weight: 800; color: #2F6BFF; text-decoration: underline; text-underline-offset: 3px; font-size: 13.5px; letter-spacing: -0.2px;">
                  #{{ $ticket->ticket_id }}
                </a>
              </td>
              <td>
                <div style="font-weight: 800; color: var(--cmp-text-main); font-size: 13.5px;">{{ $ticket->company?->name ?? 'Unknown Company' }}</div>
                <div style="font-size: 11px; font-weight: 700; color: var(--cmp-text-muted);">{{ $ticket->company?->company_code ?? 'C-CODE' }}</div>
              </td>
              <td>
                <div style="font-weight: 700; color: var(--cmp-text-main); font-size: 13.5px;">{{ $ticket->raised_by_name }}</div>
                <div style="font-size: 12px; font-weight: 600; color: var(--cmp-text-muted);">{{ $ticket->raised_by_email }}</div>
              </td>
              <td style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $ticket->subject }}">
                <span style="font-weight: 700; color: var(--cmp-text-main); font-size: 13.5px;">{{ $ticket->subject }}</span>
              </td>
              <td>
                <span class="badge-category">
                  {{ $ticket->category }}
                </span>
              </td>
              <td>
                <span class="badge-priority prio-{{ $ticket->priority }}">{{ $ticket->priority }}</span>
              </td>
              <td>
                <span class="badge-status status-{{ $statusKey }}">
                  <i class="bx bx-radio-circle-marked"></i> {{ str_replace('_', ' ', $statusKey) }}
                </span>
              </td>
              <td style="white-space: nowrap; font-size: 12.5px; font-weight: 600; color: var(--cmp-text-body);">
                {{ $ticket->created_at?->format('d M Y, h:i A') }}
              </td>
              <td style="white-space: nowrap; font-size: 12.5px; font-weight: 600; color: var(--cmp-text-body);">
                {{ $ticket->updated_at?->diffForHumans() }}
              </td>
              <td>
                @if($ticket->assigned_to_name && $ticket->assigned_to_name !== 'Unassigned')
                  <span class="badge-assigned">
                    <i class="bx bx-user-check"></i> {{ $ticket->assigned_to_name }}
                  </span>
                @else
                  <span class="badge-unassigned">
                    <i class="bx bx-user-x"></i> Unassigned
                  </span>
                @endif
              </td>
              <td style="text-align: right;">
                <button type="button" onclick="openComplaintDrawer({{ $ticket->id }})" class="btn-action-view">
                  <i class="bx bx-show"></i> View
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="12" style="text-align: center; padding: 48px 16px; color: var(--cmp-text-muted);">
                <i class="bx bx-inbox" style="font-size: 48px; opacity: 0.4; display: block; margin-bottom: 12px;"></i>
                <div style="font-size: 16px; font-weight: 700; color: var(--cmp-text-main);">No complaints found</div>
                <div style="font-size: 13px;">No company support tickets match your search filters.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer with Pagination -->
    <div style="padding: 16px 20px; border-top: 1px solid var(--cmp-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
      <div style="font-size: 13px; font-weight: 600; color: var(--cmp-text-muted);">
        Showing {{ $tickets->firstItem() ?? 0 }} to {{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }} entries
      </div>
      <div>
        {{ $tickets->links() }}
      </div>
    </div>
  </div>

</div>

<!-- ==================== SLIDE-OVER DETAIL DRAWER ==================== -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeComplaintDrawer()">
  <div class="slide-drawer" onclick="event.stopPropagation()">
    <div class="drawer-header">
      <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
        <div style="min-width: 0;">
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span id="drawerTicketId" style="font-size: 19px; font-weight: 900; color: #2F6BFF; letter-spacing: -0.3px;">#CMP-00000</span>
            <span id="drawerStatusBadge" class="badge-status" style="display: none; padding: 4px 10px; font-size: 10.5px;"></span>
          </div>
          <div id="drawerSubject" style="font-size: 13px; color: var(--cmp-text-muted); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 440px;">Ticket Subject</div>
        </div>
      </div>
      <button type="button" onclick="closeComplaintDrawer()" title="Close Drawer" style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--cmp-text-muted); cursor: pointer; border: 1px solid var(--cmp-border); background: var(--cmp-bg-card); transition: all 0.2s ease;">
        <i class="bx bx-x" style="font-size: 22px;"></i>
      </button>
    </div>

    <div class="drawer-body" id="drawerContentBody">
      <div style="text-align: center; padding: 60px 0;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading ticket details...</span>
        </div>
        <div style="margin-top: 12px; font-size: 13px; color: var(--cmp-text-muted); font-weight: 600;">Fetching live database record...</div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll(master) {
  const checkboxes = document.querySelectorAll('.ticket-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = master.checked;
  });
  updateSelectedCount();
}

function updateSelectedCount() {
  const checked = document.querySelectorAll('.ticket-checkbox:checked');
  const count = checked.length;
  const toolbar = document.getElementById('selectionToolbar');
  const countDisplay = document.getElementById('selectedCount');

  if (toolbar && countDisplay) {
    if (count > 0) {
      toolbar.style.display = 'flex';
      countDisplay.innerText = count;
    } else {
      toolbar.style.display = 'none';
    }
  }
}

function clearSelection() {
  const master = document.getElementById('selectAllComplaints');
  if (master) master.checked = false;
  
  const checkboxes = document.querySelectorAll('.ticket-checkbox');
  checkboxes.forEach(cb => cb.checked = false);
  updateSelectedCount();
}

function openComplaintDrawer(id) {
  const overlay = document.getElementById('drawerOverlay');
  const content = document.getElementById('drawerContentBody');

  if (!overlay || !content) return;

  overlay.classList.add('active');
  content.innerHTML = `
    <div style="text-align: center; padding: 60px 0;">
      <i class="bx bx-loader-alt bx-spin" style="font-size: 36px; color: #2F6BFF;"></i>
      <div style="margin-top: 12px; font-size: 13px; color: var(--cmp-text-muted); font-weight: 600;">Loading complaint details...</div>
    </div>
  `;

  fetch(`/superadmin/complaints/${id}`, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success && data.html) {
      content.innerHTML = data.html;
      document.getElementById('drawerTicketId').innerText = '#' + data.ticket.ticket_id;
      document.getElementById('drawerSubject').innerText = data.ticket.subject;
      if (data.ticket.status) {
        const badge = document.getElementById('drawerStatusBadge');
        if (badge) {
          badge.style.display = 'inline-flex';
          badge.className = 'badge-status status-' + data.ticket.status.replace(/ /g, '_');
          badge.innerHTML = `<i class="bx bx-radio-circle-marked"></i> ${data.ticket.status}`;
        }
      }
    } else {
      content.innerHTML = `<div class="alert alert-danger">Failed to load ticket details.</div>`;
    }
  })
  .catch(err => {
    content.innerHTML = `<div class="alert alert-danger">Error retrieving ticket data.</div>`;
  });
}

function closeComplaintDrawer() {
  const overlay = document.getElementById('drawerOverlay');
  if (overlay) overlay.classList.remove('active');
}

function toggleExportMenu(e) {
  e.stopPropagation();
  const menu = document.getElementById('exportMenuDropdown');
  if (menu) {
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
  }
}

document.addEventListener('click', function(e) {
  const menu = document.getElementById('exportMenuDropdown');
  if (menu && !menu.contains(e.target)) {
    menu.style.display = 'none';
  }
});
</script>
@endpush
