@extends('layouts.superadmin')

@section('title', 'Notification & Alert Center')

@push('styles')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');

  :root {
    --em:        #0f744c;
    --em-dark:   #073a26;
    --em-light:  #10b981;
    --em-soft:   #e4f3eb;
    --em-glow:   rgba(15,116,76,0.18);
    --sl-900:    #0f172a;
    --sl-700:    #334155;
    --sl-500:    #64748b;
    --sl-300:    #cbd5e1;
    --sl-200:    #e2e8f0;
    --sl-100:    #f1f5f9;
    --sl-50:     #f8fafc;
    --surface:   #ffffff;
    --radius:    12px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.07);
    --shadow-lg: 0 10px 28px rgba(0,0,0,0.1);
  }

  .nc-wrap {
    padding: 10px 0 56px;
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    color: var(--sl-900);
    max-width: 1600px;
    margin: 0 auto;
  }

  /* ── HEADER ── */
  .nc-header {
    background: linear-gradient(135deg, #073a26 0%, #0f744c 60%, #10b981 100%);
    border-radius: 18px;
    padding: 26px 30px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 14px 38px -4px rgba(15,116,76,0.28);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
    position: relative;
    overflow: hidden;
  }
  .nc-header::before {
    content: '';
    position: absolute; top: -50px; right: -50px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,0.06); border-radius: 50%; pointer-events: none;
  }
  .nc-header::after {
    content: '';
    position: absolute; bottom: -70px; left: 30%;
    width: 320px; height: 180px;
    background: rgba(255,255,255,0.04); border-radius: 50%; pointer-events: none;
  }
  .nc-header-left .breadcrumb {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600; opacity: 0.75; margin-bottom: 8px;
  }
  .nc-header-left .breadcrumb i { font-size: 9px; }
  .nc-header-left h1 {
    font-size: 23px; font-weight: 900; margin-bottom: 5px; letter-spacing: -0.3px;
    display: flex; align-items: center; gap: 10px;
  }
  .live-badge {
    font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px;
    background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3);
    display: inline-flex; align-items: center; gap: 5px;
  }
  .live-badge .dot { width: 7px; height: 7px; border-radius: 50%; background: #fff; animation: blink 1.5s infinite; }
  @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
  .nc-header-left p {
    font-size: 13px; opacity: 0.85; margin: 0; font-weight: 500;
  }
  .nc-header-left .meta {
    font-size: 12px; opacity: 0.65; margin-top: 6px;
    display: flex; align-items: center; gap: 6px;
  }
  .nc-header-right {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap; position: relative; z-index: 1;
  }
  .btn-glass {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.28);
    color: #fff;
    padding: 8px 16px;
    border-radius: 9px;
    font-size: 12.5px; font-weight: 700;
    cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 6px;
    transition: all 0.2s;
    text-decoration: none;
  }
  .btn-glass:hover { background: rgba(255,255,255,0.25); transform: translateY(-1px); color: #fff; }
  .btn-glass-primary {
    background: rgba(255,255,255,0.95); color: var(--em); font-weight: 800;
    border: none; padding: 8px 16px; border-radius: 9px; font-size: 12.5px;
    cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 6px;
    transition: all 0.2s;
  }
  .btn-glass-primary:hover { background: #fff; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,0,0,0.15); }

  /* ── KPI GRID ── */
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
  }
  .kpi-card {
    background: var(--surface);
    border: 1px solid var(--sl-200);
    border-radius: var(--radius);
    padding: 16px 20px;
    box-shadow: var(--shadow-sm);
    display: flex; align-items: center; gap: 14px;
    cursor: pointer;
    transition: all 0.2s;
  }
  .kpi-card:hover, .kpi-card.active-filter {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(15,116,76,0.1);
    border-color: var(--em-light);
  }
  .kpi-card.active-filter { border-color: var(--em); background: var(--em-soft); }
  .kpi-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
  }
  .kpi-val { font-size: 26px; font-weight: 900; line-height: 1.1; letter-spacing: -0.5px; }
  .kpi-label { font-size: 11.5px; color: var(--sl-500); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }

  /* ── FILTER TOOLBAR ── */
  .filter-bar {
    background: var(--surface);
    border: 1px solid var(--sl-200);
    border-radius: var(--radius);
    padding: 14px 18px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
    display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;
  }
  .filter-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
  .filter-input {
    padding: 7px 12px;
    border: 1px solid var(--sl-300);
    border-radius: 8px;
    font-size: 12.5px; color: var(--sl-900);
    background: var(--surface);
    outline: none; font-family: inherit;
    transition: border 0.15s, box-shadow 0.15s;
  }
  .filter-input:focus {
    border-color: var(--em);
    box-shadow: 0 0 0 3px var(--em-glow);
  }
  .search-wrap { position: relative; }
  .search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--sl-500); }
  .search-wrap input { padding-left: 30px; width: 220px; }
  .btn-filter {
    background: linear-gradient(135deg, var(--em-dark), var(--em));
    color: #fff; border: none; padding: 7px 16px; border-radius: 8px;
    font-size: 12.5px; font-weight: 700; font-family: inherit; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;
  }
  .btn-filter:hover { box-shadow: 0 6px 16px rgba(15,116,76,0.3); transform: translateY(-1px); }
  .btn-clear {
    background: var(--surface); border: 1px solid var(--sl-300); color: var(--sl-700);
    padding: 7px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;
    cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 5px;
    transition: all 0.15s;
  }
  .btn-clear:hover { background: var(--sl-100); }

  /* ── FEED LIST ── */
  .notif-feed { display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; }
  .notif-card {
    background: var(--surface);
    border: 1px solid var(--sl-200);
    border-radius: var(--radius);
    padding: 18px 22px;
    box-shadow: var(--shadow-sm);
    display: flex; align-items: flex-start; gap: 16px;
    position: relative;
    transition: all 0.2s;
  }
  .notif-card:hover { box-shadow: var(--shadow-md); border-color: #a7f3d0; transform: translateY(-1px); }
  .notif-card.is-unread {
    border-left: 4px solid var(--em);
    background: linear-gradient(to right, rgba(228,243,235,0.5) 0%, #fff 28%);
  }
  .notif-card.is-critical { border-left: 4px solid #dc2626; }
  .notif-card.is-warning  { border-left: 4px solid #d97706; }
  .notif-card.is-resolved { opacity: 0.72; background: #fafafa; }

  .unread-dot {
    position: absolute; top: 18px; right: 18px;
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--em);
    box-shadow: 0 0 0 3px rgba(15,116,76,0.18);
    animation: ping 2s infinite;
  }
  @keyframes ping {
    0%,100% { transform: scale(1); opacity: 1; }
    50%      { transform: scale(1.35); opacity: 0.4; }
  }

  /* Severity Icon */
  .sev-icon {
    width: 42px; height: 42px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 19px; flex-shrink: 0; box-shadow: 0 2px 7px rgba(0,0,0,0.07);
  }
  .si-critical { background: #fef2f2; color: #dc2626; }
  .si-warning  { background: #fffbeb; color: #d97706; }
  .si-info     { background: #eff6ff; color: #2563eb; }
  .si-success  { background: #ecfdf5; color: #059669; }
  .si-CRITICAL { background: #f5f3ff; color: #7c3aed; animation: pulse-c 2s infinite; }
  .si-WARNING  { background: #fffbeb; color: #d97706; }
  .si-INFO     { background: #eff6ff; color: #2563eb; }
  .si-SUCCESS  { background: #ecfdf5; color: #059669; }
  .si-ERROR    { background: #fef2f2; color: #dc2626; }
  @keyframes pulse-c {
    0%,100% { box-shadow: 0 0 0 0 rgba(124,58,237,0.3); }
    50%      { box-shadow: 0 0 0 6px rgba(124,58,237,0); }
  }

  /* Badges */
  .sev-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 20px;
    font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px;
  }
  .sb-critical { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
  .sb-warning  { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
  .sb-info     { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
  .sb-success  { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
  .sb-CRITICAL { background: #f5f3ff; color: #5b21b6; border: 1px solid #c4b5fd; }
  .sb-WARNING  { background: #fffbeb; color: #92400e; border: 1px solid #fcd34d; }
  .sb-INFO     { background: #eff6ff; color: #1d4ed8; border: 1px solid #93c5fd; }
  .sb-SUCCESS  { background: #ecfdf5; color: #047857; border: 1px solid #6ee7b7; }
  .sb-ERROR    { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }

  .cat-pill {
    background: var(--sl-100); color: var(--sl-700);
    font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px;
    text-transform: uppercase;
  }
  .company-chip {
    background: var(--em-soft); color: var(--em-dark);
    font-size: 10.5px; font-weight: 700; padding: 2px 9px; border-radius: 6px;
    display: inline-flex; align-items: center; gap: 4px;
  }
  .action-req-chip {
    background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;
    font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 4px;
    display: inline-flex; align-items: center; gap: 4px;
  }

  .notif-title  { font-size: 15px; font-weight: 800; color: var(--sl-900); margin: 0 0 5px 0; letter-spacing: -0.2px; }
  .notif-body   { font-size: 13px; color: var(--sl-500); line-height: 1.55; margin: 0 0 8px 0; font-weight: 500; }
  .notif-time   { font-size: 11px; color: #94a3b8; font-weight: 600; display: flex; align-items: center; gap: 4px; margin-left: auto; white-space: nowrap; }

  /* Sub intel box */
  .sub-intel {
    background: var(--sl-50); border: 1px solid var(--sl-200);
    border-radius: 9px; padding: 12px 14px; margin-top: 10px;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
  }
  .sub-stat { }
  .sub-stat-label { font-size: 10.5px; color: var(--sl-500); font-weight: 700; text-transform: uppercase; }
  .sub-stat-val { font-size: 13px; font-weight: 800; color: var(--sl-900); }

  /* Company mini bar */
  .company-bar {
    display: flex; align-items: center; gap: 8px;
    font-size: 12.5px; color: var(--sl-500); font-weight: 600;
  }
  .co-avatar {
    width: 24px; height: 24px; border-radius: 6px;
    background: var(--sl-100); color: var(--sl-700);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 10.5px; overflow: hidden; border: 1px solid var(--sl-200); flex-shrink: 0;
  }
  .co-code {
    font-family: monospace; font-size: 10.5px; background: var(--sl-100);
    color: var(--sl-700); padding: 1px 6px; border-radius: 4px;
  }

  /* Right-side card action column */
  .card-actions { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; flex-shrink: 0; }

  .btn-inspect {
    background: var(--surface); color: var(--sl-700);
    border: 1px solid var(--sl-300); padding: 5px 10px; border-radius: 8px;
    font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 5px; transition: all 0.15s;
  }
  .btn-inspect:hover { background: var(--em-soft); border-color: var(--em); color: var(--em); }
  .btn-mark-read {
    background: var(--surface); color: var(--sl-700);
    border: 1px solid var(--sl-300); padding: 4px 9px; border-radius: 7px;
    font-size: 11.5px; font-weight: 700; cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s;
  }
  .btn-mark-read:hover { background: var(--em-soft); border-color: var(--em); color: var(--em); }
  .btn-resolve {
    background: var(--surface); color: #059669;
    border: 1px solid #a7f3d0; padding: 4px 9px; border-radius: 7px;
    font-size: 11.5px; font-weight: 700; cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s;
  }
  .btn-resolve:hover { background: #ecfdf5; border-color: #059669; }
  .resolved-label {
    font-size: 11.5px; color: #059669; font-weight: 700;
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 9px; background: #ecfdf5; border-radius: 7px; border: 1px solid #a7f3d0;
  }
  .read-label {
    font-size: 11.5px; color: var(--sl-500); font-weight: 600;
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 9px; background: var(--sl-50); border-radius: 7px; border: 1px solid var(--sl-200);
  }
  .view-resource-link {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;
    background: var(--em-soft); color: var(--em); border: 1px solid rgba(15,116,76,0.22);
    text-decoration: none; margin-top: 8px; transition: all 0.15s;
  }
  .view-resource-link:hover { background: var(--em); color: #fff; text-decoration: none; }

  /* ── EMPTY STATES ── */
  .empty-state {
    background: var(--surface); border: 1px solid var(--sl-200);
    border-radius: 16px; padding: 56px 48px; text-align: center;
    box-shadow: var(--shadow-sm);
  }
  .empty-icon {
    width: 70px; height: 70px; border-radius: 18px;
    background: var(--em-soft); color: var(--em);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 30px;
  }
  .empty-title { font-size: 17px; font-weight: 800; color: var(--sl-900); margin: 0 0 6px; }
  .empty-sub   { font-size: 13px; color: var(--sl-500); margin: 0 0 20px; }

  /* ── DRAWER ── */
  .drawer-overlay {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(15,23,42,0.42); backdrop-filter: blur(4px);
    z-index: 1000; opacity: 0; visibility: hidden;
    transition: all 0.25s; display: flex; justify-content: flex-end;
  }
  .drawer-overlay.open { opacity: 1; visibility: visible; }
  .drawer-panel {
    width: 520px; max-width: 90vw; height: 100vh;
    background: var(--surface); box-shadow: -10px 0 40px rgba(0,0,0,0.12);
    transform: translateX(100%);
    transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
    display: flex; flex-direction: column;
  }
  .drawer-panel.open { transform: translateX(0); }
  .drawer-head {
    padding: 18px 22px; border-bottom: 1px solid var(--sl-200);
    background: var(--sl-50);
    display: flex; align-items: center; justify-content: space-between;
  }
  .drawer-body { padding: 22px; overflow-y: auto; flex: 1; }
  .drawer-footer {
    padding: 14px 22px; background: var(--sl-50);
    border-top: 1px solid var(--sl-200);
    display: flex; align-items: center; justify-content: space-between;
  }
  .drawer-section-label {
    font-size: 10.5px; font-weight: 800; color: var(--sl-500);
    text-transform: uppercase; letter-spacing: 0.6px; margin: 0 0 9px;
  }
  .drawer-info-box {
    background: var(--sl-50); border: 1px solid var(--sl-200);
    border-radius: 9px; padding: 12px 14px; margin-bottom: 18px;
  }
  .drawer-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; margin-bottom: 18px; }
  .drawer-grid-cell {
    background: var(--sl-50); border: 1px solid var(--sl-200);
    border-radius: 8px; padding: 11px 13px;
  }
  .drawer-grid-cell .lbl { font-size: 10.5px; color: var(--sl-500); font-weight: 700; text-transform: uppercase; }
  .drawer-grid-cell .val { font-size: 14px; font-weight: 800; margin-top: 2px; }

  .timeline-item {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 8px 11px; background: var(--sl-50); border-radius: 7px;
    border: 1px solid var(--sl-200); font-size: 12px; margin-bottom: 6px;
  }

  /* ── MODAL ── */
  .modal-backdrop {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(15,23,42,0.42); backdrop-filter: blur(4px);
    z-index: 1050; display: none; align-items: center; justify-content: center;
  }
  .modal-box {
    background: var(--surface); border-radius: 14px; width: 540px; max-width: 92vw;
    overflow: hidden; box-shadow: var(--shadow-lg); border: 1px solid var(--sl-200);
  }
  .modal-head {
    padding: 16px 22px; background: var(--sl-50); border-bottom: 1px solid var(--sl-200);
    display: flex; align-items: center; justify-content: space-between;
  }
  .modal-body { padding: 22px; display: flex; flex-direction: column; gap: 12px; }
  .modal-foot {
    padding: 13px 22px; background: var(--sl-50); border-top: 1px solid var(--sl-200);
    display: flex; align-items: center; justify-content: flex-end; gap: 10px;
  }
  .pref-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 14px; background: var(--sl-50); border-radius: 8px;
    border: 1px solid var(--sl-200);
  }
  .pref-row strong { font-size: 13px; color: var(--sl-900); display: block; }
  .pref-row span   { font-size: 11.5px; color: var(--sl-500); }

  /* Action buttons (general small) */
  .btn-sm-primary {
    background: var(--em); color: #fff; border: none;
    padding: 6px 14px; border-radius: 8px;
    font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s;
  }
  .btn-sm-primary:hover { background: var(--em-dark); }
  .btn-sm-secondary {
    background: var(--surface); color: var(--sl-900);
    border: 1px solid var(--sl-300); padding: 6px 14px; border-radius: 8px;
    font-size: 12.5px; font-weight: 600; cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s;
  }
  .btn-sm-secondary:hover { background: var(--sl-100); }
</style>
@endpush

@section('content')
<div class="nc-wrap">

  {{-- ── HEADER ── --}}
  <div class="nc-header">
    <div class="nc-header-left">
      <div class="breadcrumb">
        <span>Platform</span>
        <i class="bx bx-chevron-right"></i>
        <span>Monitoring</span>
        <i class="bx bx-chevron-right"></i>
        <span style="opacity:1;">Notification Center</span>
      </div>
      <h1>
        Notification &amp; Alert Center
        <span class="live-badge"><span class="dot"></span> Live</span>
      </h1>
      <p>Monitor critical events, subscription risks, system issues, and tenant activity across the platform.</p>
      <div class="meta">
        <i class="bx bx-time-five"></i>
        Last updated: <strong id="lastUpdatedTime">{{ now()->format('H:i:s') }}</strong>
      </div>
    </div>
    <div class="nc-header-right">
      <button class="btn-glass" id="markAllReadBtn">
        <i class="bx bx-check-double"></i> Mark All as Read
      </button>
      <button class="btn-glass" id="refreshBtn">
        <i class="bx bx-refresh"></i> Refresh
      </button>
      <button class="btn-glass-primary" id="openPrefsBtn">
        <i class="bx bx-sliders"></i> Preferences
      </button>
    </div>
  </div>

  {{-- ── KPI GRID ── --}}
  <div class="kpi-grid">
    <div class="kpi-card" data-ft="severity" data-fv="critical">
      <div class="kpi-icon" style="background:#fef2f2;color:#dc2626;"><i class="bx bx-error-circle"></i></div>
      <div>
        <div class="kpi-val" style="color:#dc2626;">{{ $kpis['critical'] }}</div>
        <div class="kpi-label">Critical Alerts</div>
      </div>
    </div>
    <div class="kpi-card" data-ft="severity" data-fv="warning">
      <div class="kpi-icon" style="background:#fffbeb;color:#d97706;"><i class="bx bx-error"></i></div>
      <div>
        <div class="kpi-val" style="color:#d97706;">{{ $kpis['warnings'] }}</div>
        <div class="kpi-label">System Warnings</div>
      </div>
    </div>
    <div class="kpi-card" data-ft="status" data-fv="unread">
      <div class="kpi-icon" style="background:#eff6ff;color:#2563eb;"><i class="bx bx-bell"></i></div>
      <div>
        <div class="kpi-val" style="color:#2563eb;" id="headerUnreadCount">{{ $kpis['unread'] }}</div>
        <div class="kpi-label">Unread</div>
      </div>
    </div>
    <div class="kpi-card" data-ft="category" data-fv="subscription">
      <div class="kpi-icon" style="background:#f5f3ff;color:#8b5cf6;"><i class="bx bx-calendar-alt"></i></div>
      <div>
        <div class="kpi-val" style="color:#8b5cf6;">{{ $kpis['subscription_expiring'] }}</div>
        <div class="kpi-label">Subscriptions Expiring</div>
      </div>
    </div>
    <div class="kpi-card" data-ft="category" data-fv="database">
      <div class="kpi-icon" style="background:#f1f5f9;color:#334155;"><i class="bx bx-server"></i></div>
      <div>
        <div class="kpi-val" style="color:#334155;">{{ $kpis['infrastructure_issues'] }}</div>
        <div class="kpi-label">Infrastructure Issues</div>
      </div>
    </div>
    <div class="kpi-card" data-ft="status" data-fv="resolved">
      <div class="kpi-icon" style="background:#ecfdf5;color:#059669;"><i class="bx bx-check-circle"></i></div>
      <div>
        <div class="kpi-val" style="color:#059669;">{{ $kpis['resolved_today'] }}</div>
        <div class="kpi-label">Resolved Today</div>
      </div>
    </div>
  </div>

  {{-- ── FILTER TOOLBAR ── --}}
  <div class="filter-bar">
    <div class="filter-group">
      <div class="search-wrap">
        <i class="bx bx-search"></i>
        <input type="text" class="filter-input" id="ncSearch"
               placeholder="Search title, company, category..." style="width:230px;" />
      </div>
      <select class="filter-input" id="fcSeverity">
        <option value="all">All Severities</option>
        <option value="critical">🔴 Critical</option>
        <option value="warning">⚠️ Warning</option>
        <option value="info">ℹ️ Info</option>
        <option value="success">✓ Success</option>
      </select>
      <select class="filter-input" id="fcCategory">
        <option value="all">All Categories</option>
        <option value="subscription">Subscription</option>
        <option value="company">Company</option>
        <option value="database">Database</option>
        <option value="backup">Backup</option>
        <option value="migration">Migration</option>
        <option value="usage">Usage</option>
        <option value="security">Security</option>
        <option value="system">System</option>
      </select>
      <select class="filter-input" id="fcStatus">
        <option value="all">All Statuses</option>
        <option value="unread">Unread</option>
        <option value="read">Read</option>
        <option value="resolved">Resolved</option>
        <option value="action_required">Action Required</option>
      </select>
      <select class="filter-input" id="fcCompany">
        <option value="all">All Companies</option>
        @foreach($companies as $comp)
          <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->company_code ?? 'TEN' }})</option>
        @endforeach
      </select>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
      <button class="btn-filter" id="doFilter"><i class="bx bx-filter-alt"></i> Filter</button>
      <button class="btn-clear" id="clearFilter"><i class="bx bx-x"></i> Clear</button>
    </div>
  </div>

  {{-- ── NOTIFICATION FEED ── --}}
  <div class="notif-feed" id="ncFeed">
    @foreach($allAlerts as $alert)
      <div class="notif-card
                  {{ $alert['status'] === 'unread'   ? 'is-unread'   : '' }}
                  {{ $alert['status'] === 'resolved' ? 'is-resolved' : '' }}
                  {{ $alert['severity'] === 'critical' ? 'is-critical' : ($alert['severity'] === 'warning' ? 'is-warning' : '') }}"
           data-id="{{ $alert['id'] }}"
           data-severity="{{ $alert['severity'] }}"
           data-category="{{ $alert['category'] }}"
           data-status="{{ $alert['status'] }}"
           data-company-id="{{ $alert['company_id'] ?? '' }}"
           data-action-required="{{ !empty($alert['action_required']) ? 'true' : 'false' }}"
           data-search="{{ strtolower($alert['title'] . ' ' . $alert['description'] . ' ' . $alert['company_name'] . ' ' . ($alert['tenant_code'] ?? '')) }}">

        @if($alert['status'] === 'unread')
          <span class="unread-dot" title="Unread"></span>
        @endif

        {{-- Severity icon --}}
        <div class="sev-icon si-{{ $alert['severity'] }}">
          @if($alert['severity'] === 'critical')     <i class="bx bx-error-circle"></i>
          @elseif($alert['severity'] === 'warning')  <i class="bx bx-error"></i>
          @elseif($alert['severity'] === 'success')  <i class="bx bx-check-circle"></i>
          @else                                      <i class="bx bx-info-circle"></i>
          @endif
        </div>

        {{-- Body --}}
        <div style="flex:1;min-width:0;">
          <div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
            <span class="sev-badge sb-{{ $alert['severity'] }}">● {{ strtoupper($alert['severity']) }}</span>
            <span class="cat-pill">{{ strtoupper($alert['category']) }}</span>
            @if(!empty($alert['action_required']))
              <span class="action-req-chip"><i class="bx bx-hand"></i> Action Required</span>
            @endif
            <span class="notif-time">
              <i class="bx bx-time-five" style="font-size:12px;"></i> {{ $alert['created_at'] }}
            </span>
          </div>

          <h3 class="notif-title">{{ $alert['title'] }}</h3>
          <p class="notif-body">{{ $alert['description'] }}</p>

          {{-- Company bar --}}
          <div class="company-bar">
            <div class="co-avatar">
              @if(!empty($alert['logo_url']))
                <img src="{{ $alert['logo_url'] }}" alt="{{ $alert['company_name'] }}"
                     style="width:100%;height:100%;object-fit:cover;">
              @else
                {{ strtoupper(substr($alert['company_name'],0,2)) }}
              @endif
            </div>
            <span>Company: <strong style="color:var(--sl-900);">{{ $alert['company_name'] }}</strong></span>
            <span class="co-code">{{ $alert['tenant_code'] ?? 'CENTRAL' }}</span>
          </div>

          {{-- Subscription intel box --}}
          @if($alert['category'] === 'subscription')
            <div class="sub-intel">
              <div style="display:flex;gap:18px;flex-wrap:wrap;align-items:center;">
                <div class="sub-stat">
                  <div class="sub-stat-label">Current Plan</div>
                  <div class="sub-stat-val" style="color:var(--em);">{{ $alert['plan_name'] ?? 'DIAMOND PLAN' }}</div>
                </div>
                <div style="width:1px;height:28px;background:var(--sl-200);"></div>
                <div class="sub-stat">
                  <div class="sub-stat-label">Expiry Date</div>
                  <div class="sub-stat-val">{{ $alert['expiry_date'] ?? 'N/A' }}</div>
                </div>
                <div style="width:1px;height:28px;background:var(--sl-200);"></div>
                <div class="sub-stat">
                  <div class="sub-stat-label">Days Remaining</div>
                  <div class="sub-stat-val" style="color:{{ ($alert['days_remaining'] ?? 30) <= 7 ? '#dc2626' : '#d97706' }};">
                    {{ $alert['days_remaining'] ?? 0 }} Days
                  </div>
                </div>
              </div>
              <div style="display:flex;gap:7px;">
                <a href="{{ Route::has('super-admin.companies.show') ? route('super-admin.companies.show', $alert['company_id']) : (Route::has('superadmin.companies.show') ? route('superadmin.companies.show', $alert['company_id']) : url('/super-admin/companies/'.$alert['company_id'])) }}"
                   class="btn-sm-secondary" style="padding:4px 10px;font-size:11.5px;">
                  <i class="bx bx-building"></i> View Company
                </a>
                <a href="{{ Route::has('super-admin.subscriptions.index') ? route('super-admin.subscriptions.index') : (Route::has('superadmin.subscriptions.index') ? route('superadmin.subscriptions.index') : url('/super-admin/subscriptions')) }}"
                   class="btn-sm-primary" style="padding:4px 10px;font-size:11.5px;">
                  <i class="bx bx-credit-card"></i> Manage Subscription
                </a>
              </div>
            </div>
          @endif

          {{-- Notification action_url if present (from CentralNotification bridge) --}}
          @if(!empty($alert['action_url']))
            <a href="{{ $alert['action_url'] }}" class="view-resource-link">
              View Resource <i class="bx bx-right-arrow-alt"></i>
            </a>
          @endif
        </div>

        {{-- Card actions column --}}
        <div class="card-actions">
          <button class="btn-inspect inspect-btn"
                  data-id="{{ $alert['id'] }}"
                  data-title="{{ $alert['title'] }}"
                  data-company-id="{{ $alert['company_id'] ?? '' }}"
                  data-company-name="{{ $alert['company_name'] }}"
                  data-tenant-code="{{ $alert['tenant_code'] ?? 'CENTRAL' }}"
                  data-domain="{{ $alert['domain'] ?? ($alert['company_name'].'.pms.com') }}"
                  data-plan="{{ $alert['plan_name'] ?? 'DIAMOND PLAN' }}"
                  data-expiry="{{ $alert['expiry_date'] ?? 'N/A' }}"
                  data-days="{{ $alert['days_remaining'] ?? 0 }}"
                  data-db="{{ $alert['db_name'] ?? 'pms_central' }}"
                  data-severity="{{ $alert['severity'] }}"
                  data-category="{{ $alert['category'] }}"
                  data-description="{{ $alert['description'] }}">
            <i class="bx bx-show" style="color:var(--em);"></i> Inspect
          </button>

          @if($alert['status'] === 'unread')
            <button class="btn-mark-read mark-read-btn" data-id="{{ $alert['id'] }}">
              <i class="bx bx-check"></i> Read
            </button>
          @else
            <span class="read-label"><i class="bx bx-check-double"></i> Read</span>
          @endif

          @if($alert['status'] !== 'resolved')
            <button class="btn-resolve resolve-btn" data-id="{{ $alert['id'] }}">
              <i class="bx bx-check-circle"></i> Resolve
            </button>
          @else
            <span class="resolved-label"><i class="bx bx-check-double"></i> Resolved</span>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  {{-- Empty State (filter) --}}
  <div class="empty-state" id="emptyFiltered" style="display:none;">
    <div class="empty-icon"><i class="bx bx-search-alt-2"></i></div>
    <div class="empty-title">No notifications match your filters</div>
    <div class="empty-sub">Try adjusting your search or filter criteria.</div>
    <button class="btn-sm-primary" id="resetFromEmpty"><i class="bx bx-undo"></i> Clear All Filters</button>
  </div>

  {{-- Empty State (no data) --}}
  @if(count($allAlerts) === 0)
    <div class="empty-state">
      <div class="empty-icon"><i class="bx bx-bell-off"></i></div>
      <div class="empty-title">No notifications found</div>
      <div class="empty-sub">Your system notification feed is completely clear.</div>
    </div>
  @endif

</div>
{{-- end nc-wrap --}}

{{-- ── INSPECT DRAWER ── --}}
<div class="drawer-overlay" id="ncDrawer">
  <div class="drawer-panel" id="ncDrawerPanel">
    <div class="drawer-head">
      <div>
        <span class="sev-badge" id="drawerSevBadge" style="margin-bottom:6px;display:inline-block;">● INFO</span>
        <h3 style="font-size:16px;font-weight:800;color:var(--sl-900);margin:5px 0 0;">
          <span id="drawerTitle">Alert Detail</span>
        </h3>
      </div>
      <button class="btn-sm-secondary" id="closeDrawer" style="padding:6px 10px;"><i class="bx bx-x"></i></button>
    </div>
    <div class="drawer-body">
      {{-- Affected company --}}
      <p class="drawer-section-label">Affected Tenant Company</p>
      <div class="drawer-info-box" style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <strong style="font-size:14px;color:var(--sl-900);display:block;" id="drawerCompany">—</strong>
          <div style="font-size:12px;color:var(--sl-500);margin-top:3px;">
            Tenant ID: <code style="font-weight:700;" id="drawerTenantCode">—</code>
            &bull; Domain: <span id="drawerDomain">—</span>
          </div>
        </div>
        <a href="#" id="drawerCompanyLink" class="btn-sm-secondary" style="padding:4px 10px;font-size:11.5px;">
          <i class="bx bx-link-external"></i> Profile
        </a>
      </div>

      {{-- Subscription specs --}}
      <p class="drawer-section-label">Subscription Specifications</p>
      <div class="drawer-grid-2">
        <div class="drawer-grid-cell">
          <div class="lbl">Current Package</div>
          <div class="val" style="color:var(--em);" id="drawerPlan">—</div>
        </div>
        <div class="drawer-grid-cell">
          <div class="lbl">Days Remaining</div>
          <div class="val" style="color:#dc2626;" id="drawerDays">—</div>
        </div>
      </div>

      {{-- Infrastructure metrics --}}
      <p class="drawer-section-label">Infrastructure Metrics</p>
      <div class="drawer-info-box" style="font-size:12.5px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
          <span style="color:var(--sl-500);">Tenant Database:</span>
          <code style="font-weight:700;" id="drawerDb">—</code>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
          <span style="color:var(--sl-500);">Database Health:</span>
          <strong style="color:#059669;" id="drawerDbHealth">Healthy (21ms)</strong>
        </div>
        <div style="display:flex;justify-content:space-between;">
          <span style="color:var(--sl-500);">Storage Usage:</span>
          <strong style="color:#d97706;" id="drawerStorage">84% Capacity</strong>
        </div>
      </div>

      {{-- Timeline --}}
      <p class="drawer-section-label">Alert Life-Cycle Timeline</p>
      <div id="drawerTimeline">
        <div class="timeline-item">
          <span><strong style="color:var(--sl-900);">Alert generated</strong></span>
          <span style="color:var(--sl-500);">Just now</span>
        </div>
      </div>
    </div>
    <div class="drawer-footer">
      <button class="btn-sm-secondary" id="drawerMarkReadBtn"><i class="bx bx-check"></i> Mark as Read</button>
      <button class="btn-sm-primary"   id="drawerResolveBtn"><i class="bx bx-check-circle"></i> Mark as Resolved</button>
    </div>
  </div>
</div>

{{-- ── PREFERENCES MODAL ── --}}
<div class="modal-backdrop" id="prefsModal">
  <div class="modal-box">
    <div class="modal-head">
      <h3 style="font-size:16px;font-weight:800;color:var(--sl-900);margin:0;">
        <i class="bx bx-sliders" style="color:var(--em);"></i> Notification Preferences
      </h3>
      <button class="btn-sm-secondary" id="closePrefsBtn" style="padding:6px 10px;"><i class="bx bx-x"></i></button>
    </div>
    <div class="modal-body">
      <div class="pref-row">
        <div>
          <strong>Super Admin Email Alerts</strong>
          <span>Receive immediate emails for Critical &amp; Action Required alerts.</span>
        </div>
        <input type="checkbox" checked style="width:18px;height:18px;cursor:pointer;">
      </div>
      <div class="pref-row">
        <div>
          <strong>Subscription Expiry Warnings</strong>
          <span>Auto-trigger warnings at 30, 15, 7, 3, and 1-day thresholds.</span>
        </div>
        <input type="checkbox" checked style="width:18px;height:18px;cursor:pointer;">
      </div>
      <div class="pref-row">
        <div>
          <strong>Storage &amp; Database Thresholds</strong>
          <span>Notify when tenant DB storage exceeds 80% or connections drop.</span>
        </div>
        <input type="checkbox" checked style="width:18px;height:18px;cursor:pointer;">
      </div>
      <div class="pref-row">
        <div>
          <strong>Tenant Complaint Alerts</strong>
          <span>Push alert when a new support ticket is opened by a tenant.</span>
        </div>
        <input type="checkbox" checked style="width:18px;height:18px;cursor:pointer;">
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn-sm-secondary" id="cancelPrefsBtn">Cancel</button>
      <button class="btn-sm-primary"   id="savePrefsBtn"><i class="bx bx-save"></i> Save Preferences</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

  /* ─── 1. FILTERING ENGINE ─── */
  const searchEl   = document.getElementById('ncSearch');
  const fcSev      = document.getElementById('fcSeverity');
  const fcCat      = document.getElementById('fcCategory');
  const fcStat     = document.getElementById('fcStatus');
  const fcComp     = document.getElementById('fcCompany');
  const clearBtn   = document.getElementById('clearFilter');
  const resetEmpty = document.getElementById('resetFromEmpty');
  const emptyState = document.getElementById('emptyFiltered');
  const cards      = document.querySelectorAll('.notif-card');
  const kpiCards   = document.querySelectorAll('.kpi-card');

  function applyFilters() {
    const q    = searchEl ? searchEl.value.toLowerCase().trim() : '';
    const sev  = fcSev  ? fcSev.value  : 'all';
    const cat  = fcCat  ? fcCat.value  : 'all';
    const stat = fcStat ? fcStat.value : 'all';
    const comp = fcComp ? fcComp.value : 'all';

    let visible = 0;
    cards.forEach(c => {
      const cs  = (c.dataset.search        || '').toLowerCase();
      const csev = c.dataset.severity      || '';
      const ccat = c.dataset.category      || '';
      const cst  = c.dataset.status        || '';
      const cco  = c.dataset.companyId     || '';
      const car  = c.dataset.actionRequired === 'true';

      const mq  = !q    || cs.includes(q);
      const ms  = sev  === 'all' || csev === sev;
      const mc  = cat  === 'all' || ccat === cat;
      const mco = comp === 'all' || cco  === comp;
      let   mst = true;
      if (stat !== 'all') {
        mst = stat === 'action_required' ? car : cst === stat;
      }

      const show = mq && ms && mc && mst && mco;
      c.style.display = show ? 'flex' : 'none';
      if (show) visible++;
    });

    if (emptyState) emptyState.style.display = visible === 0 ? 'block' : 'none';
  }

  function resetFilters() {
    if (searchEl) searchEl.value = '';
    if (fcSev)    fcSev.value   = 'all';
    if (fcCat)    fcCat.value   = 'all';
    if (fcStat)   fcStat.value  = 'all';
    if (fcComp)   fcComp.value  = 'all';
    kpiCards.forEach(k => k.classList.remove('active-filter'));
    applyFilters();
  }

  if (searchEl) searchEl.addEventListener('input', applyFilters);
  [fcSev, fcCat, fcStat, fcComp].forEach(el => { if (el) el.addEventListener('change', applyFilters); });
  document.getElementById('doFilter')?.addEventListener('click', applyFilters);
  if (clearBtn)   clearBtn.addEventListener('click', resetFilters);
  if (resetEmpty) resetEmpty.addEventListener('click', resetFilters);

  // KPI click-to-filter
  kpiCards.forEach(k => {
    k.addEventListener('click', function() {
      const ft = this.dataset.ft, fv = this.dataset.fv;
      resetFilters();
      kpiCards.forEach(x => x.classList.remove('active-filter'));
      this.classList.add('active-filter');
      if (ft === 'severity' && fcSev)  fcSev.value  = fv;
      if (ft === 'category' && fcCat)  fcCat.value  = fv;
      if (ft === 'status'   && fcStat) fcStat.value = fv;
      applyFilters();
    });
  });

  /* ─── 2. MARK READ ─── */
  document.querySelectorAll('.mark-read-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const id   = this.dataset.id;
      const card = this.closest('.notif-card');
      fetch(`/super-admin/alerts/${id}/read`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      }).then(r => r.json()).then(() => {
        card.classList.remove('is-unread');
        card.dataset.status = 'read';
        card.querySelector('.unread-dot')?.remove();
        this.replaceWith(Object.assign(document.createElement('span'), {
          className: 'read-label',
          innerHTML: '<i class="bx bx-check-double"></i> Read'
        }));
        updateUnreadCount(-1);
      });
    });
  });

  /* ─── 3. RESOLVE ─── */
  document.querySelectorAll('.resolve-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const id   = this.dataset.id;
      const card = this.closest('.notif-card');
      fetch(`/super-admin/alerts/${id}/resolve`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      }).then(r => r.json()).then(() => {
        card.classList.add('is-resolved');
        card.dataset.status = 'resolved';
        this.replaceWith(Object.assign(document.createElement('span'), {
          className: 'resolved-label',
          innerHTML: '<i class="bx bx-check-double"></i> Resolved'
        }));
      });
    });
  });

  /* ─── 4. MARK ALL READ ─── */
  document.getElementById('markAllReadBtn')?.addEventListener('click', function() {
    fetch("{{ Route::has('super-admin.alerts.mark-all-read') ? route('super-admin.alerts.mark-all-read') : (Route::has('superadmin.alerts.mark-all-read') ? route('superadmin.alerts.mark-all-read') : (Route::has('superadmin.notifications.read-all') ? route('superadmin.notifications.read-all') : url('/super-admin/alerts/mark-all-read'))) }}", {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(r => r.json()).then(() => {
      document.querySelectorAll('.notif-card.is-unread').forEach(c => {
        c.classList.remove('is-unread');
        c.dataset.status = 'read';
        c.querySelector('.unread-dot')?.remove();
      });
      document.querySelectorAll('.mark-read-btn').forEach(b => {
        b.replaceWith(Object.assign(document.createElement('span'), {
          className: 'read-label',
          innerHTML: '<i class="bx bx-check-double"></i> Read'
        }));
      });
      const cnt = document.getElementById('headerUnreadCount');
      if (cnt) cnt.textContent = '0';
      const sidebarBadge = document.getElementById('sidebarNotificationsBadge');
      if (sidebarBadge) sidebarBadge.textContent = '0';
    });
  });

  function updateUnreadCount(delta) {
    const cnt = document.getElementById('headerUnreadCount');
    if (cnt) cnt.textContent = Math.max(0, parseInt(cnt.textContent || 0) + delta);
    const sb = document.getElementById('sidebarNotificationsBadge');
    if (sb)  sb.textContent  = Math.max(0, parseInt(sb.textContent  || 0) + delta);
  }

  /* ─── 5. REFRESH ─── */
  document.getElementById('refreshBtn')?.addEventListener('click', function() {
    this.disabled = true;
    this.innerHTML = '<i class="bx bx-loader bx-spin"></i> Refreshing...';
    setTimeout(() => {
      this.disabled = false;
      this.innerHTML = '<i class="bx bx-refresh"></i> Refresh';
      const ts = document.getElementById('lastUpdatedTime');
      if (ts) ts.textContent = new Date().toTimeString().slice(0,8);
    }, 600);
  });

  /* ─── 6. INSPECT DRAWER ─── */
  const drawerOverlay = document.getElementById('ncDrawer');
  const drawerPanel   = document.getElementById('ncDrawerPanel');
  const closeDrawer   = document.getElementById('closeDrawer');
  let   activeAlertId = null;

  document.querySelectorAll('.inspect-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      activeAlertId = this.dataset.id;
      const sev   = this.dataset.severity;
      const sevCls = { critical:'sb-critical', warning:'sb-warning', info:'sb-info', success:'sb-success' };
      const badge  = document.getElementById('drawerSevBadge');
      badge.className  = 'sev-badge ' + (sevCls[sev] || 'sb-info');
      badge.textContent = '● ' + sev.toUpperCase();

      document.getElementById('drawerTitle').textContent   = this.dataset.title;
      document.getElementById('drawerCompany').textContent = this.dataset.companyName;
      document.getElementById('drawerTenantCode').textContent = this.dataset.tenantCode;
      document.getElementById('drawerDomain').textContent  = this.dataset.domain;
      document.getElementById('drawerPlan').textContent    = this.dataset.plan;
      document.getElementById('drawerDays').textContent    = this.dataset.days + ' Days';
      document.getElementById('drawerDb').textContent      = this.dataset.db;
      const compId = this.dataset.companyId || '1';
      document.getElementById('drawerCompanyLink').href    = `/super-admin/companies/${compId}`;

      drawerOverlay.classList.add('open');
      drawerPanel.classList.add('open');

      // Dynamic drawer data
      fetch(`/super-admin/alerts/details/${activeAlertId}?company_id=${compId}`)
        .then(r => r.json())
        .then(data => {
          if (data.alert) {
            const a = data.alert;
            if (a.title)         document.getElementById('drawerTitle').textContent   = a.title;
            if (a.company_name)  document.getElementById('drawerCompany').textContent = a.company_name;
            if (a.tenant_code)   document.getElementById('drawerTenantCode').textContent = a.tenant_code;
            if (a.domain)        document.getElementById('drawerDomain').textContent  = a.domain;
            if (a.plan_name)     document.getElementById('drawerPlan').textContent    = a.plan_name;
            if (a.days_remaining !== undefined) document.getElementById('drawerDays').textContent = a.days_remaining + ' Days';
            if (a.db_name)       document.getElementById('drawerDb').textContent      = a.db_name;
            if (a.db_health)     document.getElementById('drawerDbHealth').textContent = a.db_health;
            if (a.storage_usage) document.getElementById('drawerStorage').textContent  = a.storage_usage;
          }
        }).catch(() => {});
    });
  });

  function closeD() {
    drawerOverlay.classList.remove('open');
    drawerPanel.classList.remove('open');
    activeAlertId = null;
  }

  closeDrawer?.addEventListener('click', closeD);
  drawerOverlay?.addEventListener('click', e => { if (e.target === drawerOverlay) closeD(); });

  document.getElementById('drawerMarkReadBtn')?.addEventListener('click', function() {
    if (!activeAlertId) return;
    const card = document.querySelector(`.notif-card[data-id="${activeAlertId}"]`);
    fetch(`/super-admin/alerts/${activeAlertId}/read`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(r => r.json()).then(() => {
      if (card) {
        card.classList.remove('is-unread');
        card.dataset.status = 'read';
        card.querySelector('.unread-dot')?.remove();
        const rb = card.querySelector('.mark-read-btn');
        if (rb) rb.replaceWith(Object.assign(document.createElement('span'), {
          className: 'read-label', innerHTML: '<i class="bx bx-check-double"></i> Read'
        }));
        updateUnreadCount(-1);
      }
      closeD();
    });
  });

  document.getElementById('drawerResolveBtn')?.addEventListener('click', function() {
    if (!activeAlertId) return;
    const card = document.querySelector(`.notif-card[data-id="${activeAlertId}"]`);
    fetch(`/super-admin/alerts/${activeAlertId}/resolve`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(r => r.json()).then(() => {
      if (card) {
        card.classList.add('is-resolved');
        card.dataset.status = 'resolved';
        const rb = card.querySelector('.resolve-btn');
        if (rb) rb.replaceWith(Object.assign(document.createElement('span'), {
          className: 'resolved-label', innerHTML: '<i class="bx bx-check-double"></i> Resolved'
        }));
      }
      closeD();
    });
  });

  /* ─── 7. PREFERENCES MODAL ─── */
  const prefsModal = document.getElementById('prefsModal');
  document.getElementById('openPrefsBtn')?.addEventListener('click', () => prefsModal.style.display = 'flex');
  function closePrefs() { prefsModal.style.display = 'none'; }
  document.getElementById('closePrefsBtn')?.addEventListener('click', closePrefs);
  document.getElementById('cancelPrefsBtn')?.addEventListener('click', closePrefs);
  document.getElementById('savePrefsBtn')?.addEventListener('click', closePrefs);
  prefsModal?.addEventListener('click', e => { if (e.target === prefsModal) closePrefs(); });

});
</script>
@endpush
