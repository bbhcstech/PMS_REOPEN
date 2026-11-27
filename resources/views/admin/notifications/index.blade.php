@extends('admin.layout.app')

@section('content')
@php
  $kpis = $kpis ?? [
      'total' => isset($notifications) && method_exists($notifications, 'total') ? $notifications->total() : (isset($notifications) ? count($notifications) : 0),
      'unread' => isset($notifications) ? collect(method_exists($notifications, 'items') ? $notifications->items() : $notifications)->filter(fn($n) => empty($n->read_at) && empty($n->is_read))->count() : 0,
      'critical' => isset($notifications) ? collect(method_exists($notifications, 'items') ? $notifications->items() : $notifications)->filter(fn($n) => (strtoupper($n->severity ?? $n->data['severity'] ?? '') === 'CRITICAL') && empty($n->read_at) && empty($n->is_read))->count() : 0,
  ];
@endphp

<style>
  #company-notification-center {
    max-width: 1180px;
    padding: 0 0 2rem;
  }

  /* Hero Header Card */
  #company-notification-center .notif-hero-card {
    background: var(--bx-surface, #ffffff) !important;
    border: 1px solid var(--bx-border, #e2e8f0) !important;
    border-radius: 16px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
  }

  #company-notification-center .notif-hero-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #eff6ff;
    border: 1px solid rgba(37, 99, 235, 0.14);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #2563eb;
    flex-shrink: 0;
  }

  #company-notification-center .notif-hero-icon i {
    color: #2563eb !important;
    -webkit-text-fill-color: #2563eb !important;
    -webkit-text-stroke: 0 !important;
  }

  #company-notification-center .notif-hero-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--bx-ink, #0f172a) !important;
    -webkit-text-fill-color: var(--bx-ink, #0f172a) !important;
    letter-spacing: -0.02em;
    margin: 0;
  }

  #company-notification-center .notif-hero-subtitle {
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--bx-ink-muted, #64748b) !important;
    -webkit-text-fill-color: var(--bx-ink-muted, #64748b) !important;
    margin: 0.2rem 0 0;
  }

  #company-notification-center .notif-unread-pill {
    background: #fef2f2 !important;
    color: #ef4444 !important;
    -webkit-text-fill-color: #ef4444 !important;
    border: 1px solid #fecaca !important;
    border-radius: 999px !important;
    font-size: 0.7rem !important;
    font-weight: 700 !important;
    padding: 0.22rem 0.65rem !important;
    line-height: 1.2;
  }

  /* Hero Action Buttons */
  #company-notification-center .notif-mark-all-btn,
  #company-notification-center .notif-clear-all-btn {
    background: var(--bx-surface, #ffffff) !important;
    border: 1px solid var(--bx-border, #e2e8f0) !important;
    color: var(--bx-ink, #0f172a) !important;
    -webkit-text-fill-color: var(--bx-ink, #0f172a) !important;
    border-radius: 10px !important;
    font-size: 0.8rem !important;
    font-weight: 650 !important;
    padding: 0.48rem 0.95rem !important;
    min-height: 38px !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03) !important;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.18s ease;
  }

  #company-notification-center .notif-mark-all-btn i {
    color: #10b981 !important;
    -webkit-text-fill-color: #10b981 !important;
    -webkit-text-stroke: 0 !important;
    font-size: 1rem !important;
  }

  #company-notification-center .notif-clear-all-btn i {
    color: #64748b !important;
    -webkit-text-fill-color: #64748b !important;
    -webkit-text-stroke: 0 !important;
    font-size: 1rem !important;
  }

  #company-notification-center .notif-mark-all-btn:hover {
    background: #f8fafc !important;
    border-color: #2563eb !important;
    color: #2563eb !important;
    -webkit-text-fill-color: #2563eb !important;
  }

  #company-notification-center .notif-clear-all-btn:hover {
    background: #fef2f2 !important;
    border-color: #fca5a5 !important;
    color: #dc2626 !important;
    -webkit-text-fill-color: #dc2626 !important;
  }

  #company-notification-center .notif-clear-all-btn:hover i {
    color: #dc2626 !important;
    -webkit-text-fill-color: #dc2626 !important;
  }

  /* 3-Column KPI Grid */
  #company-notification-center .notification-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
  }

  #company-notification-center .notification-kpi-grid > div {
    width: auto;
    min-width: 0;
  }

  #company-notification-center .kpi-stat-card {
    height: 100%;
    border-radius: 14px !important;
    border: 1px solid var(--bx-border, #e2e8f0) !important;
    background: var(--bx-surface, #ffffff) !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03) !important;
    padding: 1.1rem 1.25rem !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  }

  #company-notification-center .kpi-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06) !important;
    border-color: var(--bx-border-strong, #cbd5e1) !important;
  }

  #company-notification-center .kpi-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
  }

  #company-notification-center .kpi-icon-box i {
    -webkit-text-stroke: 0 !important;
    font-size: 1.25rem !important;
  }

  #company-notification-center .kpi-icon-total {
    background: #eff6ff !important;
    color: #2563eb !important;
  }

  #company-notification-center .kpi-icon-total i {
    color: #2563eb !important;
    -webkit-text-fill-color: #2563eb !important;
  }

  #company-notification-center .kpi-icon-unread {
    background: #f5f3ff !important;
    color: #7c3aed !important;
  }

  #company-notification-center .kpi-icon-unread i {
    color: #7c3aed !important;
    -webkit-text-fill-color: #7c3aed !important;
  }

  #company-notification-center .kpi-icon-critical {
    background: #ecfeff !important;
    color: #0891b2 !important;
  }

  #company-notification-center .kpi-icon-critical i {
    color: #0891b2 !important;
    -webkit-text-fill-color: #0891b2 !important;
  }

  #company-notification-center .kpi-stat-card .kpi-number {
    font-family: var(--font-display, inherit);
    font-size: 1.45rem !important;
    font-weight: 800 !important;
    line-height: 1.15;
    color: var(--bx-ink, #0f172a) !important;
    -webkit-text-fill-color: var(--bx-ink, #0f172a) !important;
    letter-spacing: -0.02em;
  }

  #company-notification-center .kpi-stat-card .kpi-label {
    font-size: 0.68rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.05em !important;
    color: var(--bx-ink-muted, #64748b) !important;
    -webkit-text-fill-color: var(--bx-ink-muted, #64748b) !important;
    margin-top: 0.15rem;
  }

  /* Filter Toolbar */
  #company-notification-center .notif-filter-card {
    border-radius: 14px !important;
    background: var(--bx-surface, #ffffff) !important;
    border: 1px solid var(--bx-border, #e2e8f0) !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03) !important;
  }

  #company-notification-center .notification-filter-grid {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  #company-notification-center .notif-filter-selects {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    flex: 1 1 auto;
  }

  #company-notification-center .notif-select {
    width: 210px;
    min-height: 38px !important;
    border-radius: 10px !important;
    font-weight: 500;
    font-size: 0.82rem !important;
    background-color: #f8fafc !important;
    color: var(--bx-ink, #0f172a) !important;
    border: 1px solid var(--bx-border, #e2e8f0) !important;
    padding: 0.4rem 2rem 0.4rem 0.85rem !important;
  }

  #company-notification-center .notif-filter-btn {
    background: #0f172a !important;
    border: 1px solid #0f172a !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    border-radius: 10px !important;
    font-size: 0.82rem !important;
    font-weight: 650 !important;
    min-width: 88px;
    min-height: 38px !important;
    padding: 0.45rem 1.35rem !important;
    box-shadow: none !important;
  }

  #company-notification-center .notif-filter-btn:hover {
    background: #1e293b !important;
    border-color: #1e293b !important;
  }

  /* Notification Cards */
  #company-notification-center .notif-card-item {
    border-radius: 14px !important;
    background: var(--bx-surface, #ffffff) !important;
    border: 1px solid var(--bx-border, #e2e8f0) !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03) !important;
    padding: 1.25rem 1.4rem !important;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
  }

  #company-notification-center .notif-card-item:hover {
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06) !important;
    border-color: var(--bx-border-strong, #cbd5e1) !important;
    transform: translateY(-1px);
  }

  #company-notification-center .notif-severity-icon {
    width: 28px !important;
    height: 28px !important;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem !important;
    flex-shrink: 0;
  }

  #company-notification-center .notif-severity-icon > i.bx {
    font-family: boxicons !important;
    background: transparent !important;
    color: var(--notif-icon-color) !important;
    -webkit-text-fill-color: var(--notif-icon-color) !important;
    -webkit-text-stroke: 0 !important;
    font-size: 0.95rem !important;
  }

  #company-notification-center .notif-badge-category {
    background: #f1f5f9 !important;
    color: #334155 !important;
    -webkit-text-fill-color: #334155 !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 6px !important;
    font-weight: 700 !important;
    font-size: 0.68rem !important;
    letter-spacing: 0.04em !important;
    padding: 0.28rem 0.55rem !important;
  }

  #company-notification-center .notif-badge-severity {
    border-radius: 6px !important;
    font-weight: 700 !important;
    font-size: 0.68rem !important;
    letter-spacing: 0.03em !important;
    padding: 0.28rem 0.55rem !important;
  }

  #company-notification-center .notif-badge-status-unread {
    background: #fef3c7 !important;
    color: #b45309 !important;
    -webkit-text-fill-color: #b45309 !important;
    border: 1px solid #fde68a !important;
    border-radius: 6px !important;
    font-weight: 700 !important;
    font-size: 0.68rem !important;
    padding: 0.28rem 0.6rem !important;
  }

  #company-notification-center .notif-badge-status-read {
    background: #dcfce7 !important;
    color: #15803d !important;
    -webkit-text-fill-color: #15803d !important;
    border: 1px solid #bbf7d0 !important;
    border-radius: 6px !important;
    font-weight: 700 !important;
    font-size: 0.68rem !important;
    padding: 0.28rem 0.6rem !important;
  }

  #company-notification-center .notif-time {
    font-size: 0.76rem !important;
    font-weight: 500 !important;
    color: var(--bx-ink-muted, #64748b) !important;
    -webkit-text-fill-color: var(--bx-ink-muted, #64748b) !important;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
  }

  #company-notification-center .notif-time i {
    color: var(--bx-ink-faint, #94a3b8) !important;
    -webkit-text-fill-color: var(--bx-ink-faint, #94a3b8) !important;
    -webkit-text-stroke: 0 !important;
    font-size: 0.9rem !important;
  }

  #company-notification-center .btn-pill-action {
    background: var(--bx-surface, #ffffff) !important;
    border: 1px solid var(--bx-border, #e2e8f0) !important;
    color: var(--bx-ink-body, #334155) !important;
    -webkit-text-fill-color: var(--bx-ink-body, #334155) !important;
    font-size: 0.76rem !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    padding: 0.3rem 0.75rem !important;
    min-height: 30px !important;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.16s ease;
  }

  #company-notification-center .btn-pill-action i {
    -webkit-text-stroke: 0 !important;
    font-size: 0.92rem !important;
  }

  #company-notification-center .notif-btn-inspect i {
    color: #64748b !important;
    -webkit-text-fill-color: #64748b !important;
  }

  #company-notification-center .notif-btn-read i {
    color: #10b981 !important;
    -webkit-text-fill-color: #10b981 !important;
  }

  #company-notification-center .btn-pill-action:hover {
    background: #f8fafc !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
    -webkit-text-fill-color: #0f172a !important;
  }

  #company-notification-center .notif-btn-delete i {
    color: #dc2626 !important;
    -webkit-text-fill-color: #dc2626 !important;
  }

  #company-notification-center .notif-btn-delete:hover {
    background: #fef2f2 !important;
    border-color: #fca5a5 !important;
    color: #dc2626 !important;
    -webkit-text-fill-color: #dc2626 !important;
  }

  #company-notification-center .notif-delete-selected-btn {
    background: #fef2f2 !important;
    border: 1px solid #fecaca !important;
    color: #dc2626 !important;
    -webkit-text-fill-color: #dc2626 !important;
    border-radius: 10px !important;
    font-size: 0.8rem !important;
    font-weight: 650 !important;
    padding: 0.48rem 0.95rem !important;
    min-height: 38px !important;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.18s ease;
  }

  #company-notification-center .notif-delete-selected-btn i {
    color: #dc2626 !important;
    -webkit-text-fill-color: #dc2626 !important;
    -webkit-text-stroke: 0 !important;
    font-size: 1rem !important;
  }

  #company-notification-center .notif-delete-selected-btn:hover {
    background: #fee2e2 !important;
    border-color: #fca5a5 !important;
  }

  #company-notification-center .notif-select-checkbox {
    width: 16px;
    height: 16px;
    margin: 0;
    cursor: pointer;
    accent-color: #2563eb;
    flex-shrink: 0;
  }

  #company-notification-center .notif-select-all-label {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--bx-ink-body, #334155);
    cursor: pointer;
    user-select: none;
    margin: 0;
  }
  #company-notification-center .notif-title {
    font-size: 0.98rem !important;
    font-weight: 750 !important;
    color: var(--bx-ink, #0f172a) !important;
    -webkit-text-fill-color: var(--bx-ink, #0f172a) !important;
    letter-spacing: -0.015em;
  }

  #company-notification-center .notif-message {
    font-size: 0.84rem !important;
    line-height: 1.5 !important;
    font-weight: 500 !important;
    color: var(--bx-ink-body, #475569) !important;
    -webkit-text-fill-color: var(--bx-ink-body, #475569) !important;
  }

  /* Subscription Embedded Box */
  #company-notification-center .notif-sub-box {
    background: var(--bx-surface-2, #f8fafc) !important;
    border: 1px solid var(--bx-border, #e2e8f0) !important;
    border-radius: 10px !important;
    padding: 0.85rem 1.2rem !important;
  }

  #company-notification-center .notif-sub-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
  }

  #company-notification-center .notif-sub-label {
    font-size: 0.64rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.05em !important;
    color: var(--bx-ink-muted, #64748b) !important;
    -webkit-text-fill-color: var(--bx-ink-muted, #64748b) !important;
  }

  #company-notification-center .notif-sub-val-plan {
    font-size: 0.84rem !important;
    font-weight: 750 !important;
    color: #2563eb !important;
    -webkit-text-fill-color: #2563eb !important;
  }

  #company-notification-center .notif-sub-val-expiry {
    font-size: 0.84rem !important;
    font-weight: 750 !important;
    color: var(--bx-ink, #0f172a) !important;
    -webkit-text-fill-color: var(--bx-ink, #0f172a) !important;
  }

  #company-notification-center .notif-sub-val-days {
    font-size: 0.84rem !important;
    font-weight: 750 !important;
  }

  #company-notification-center .notif-inspect-box {
    background: var(--bx-surface-2, #f8fafc) !important;
    border: 1px solid var(--bx-border, #e2e8f0) !important;
    color: var(--bx-ink-body, #334155) !important;
  }

  #company-notification-center .notif-empty-card {
    border-radius: 16px !important;
    background: var(--bx-surface, #ffffff) !important;
    border: 1px solid var(--bx-border, #e2e8f0) !important;
  }

  @media (max-width: 767.98px) {
    #company-notification-center {
      padding: 0 0 1.5rem !important;
    }
    #company-notification-center .notification-kpi-grid,
    #company-notification-center .notif-sub-grid {
      grid-template-columns: minmax(0, 1fr);
    }
    #company-notification-center .notif-select {
      width: 100%;
    }
    #company-notification-center .notif-filter-btn {
      width: 100%;
    }
  }

  /* --------------------------------------------------------------------------
     DARK MODE VISIBILITY RULES FOR COMPANY NOTIFICATION CENTER
     -------------------------------------------------------------------------- */
  html[data-pms-theme="dark"] #company-notification-center .notif-hero-card,
  html[data-pms-theme="dark"] #company-notification-center .kpi-stat-card,
  html[data-pms-theme="dark"] #company-notification-center .notif-filter-card,
  html[data-pms-theme="dark"] #company-notification-center .notif-card-item,
  html[data-pms-theme="dark"] #company-notification-center .notif-empty-card {
    background: #0f1530 !important;
    border-color: rgba(238, 241, 251, 0.1) !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35) !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-hero-icon,
  html[data-pms-theme="dark"] #company-notification-center .kpi-icon-total {
    background: rgba(47, 107, 255, 0.18) !important;
    border-color: rgba(96, 165, 250, 0.25) !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-hero-icon i,
  html[data-pms-theme="dark"] #company-notification-center .kpi-icon-total i {
    color: #60a5fa !important;
    -webkit-text-fill-color: #60a5fa !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .kpi-icon-unread {
    background: rgba(139, 92, 246, 0.2) !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .kpi-icon-unread i {
    color: #a78bfa !important;
    -webkit-text-fill-color: #a78bfa !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .kpi-icon-critical {
    background: rgba(6, 182, 212, 0.18) !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .kpi-icon-critical i {
    color: #22d3ee !important;
    -webkit-text-fill-color: #22d3ee !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-unread-pill {
    background: rgba(239, 68, 68, 0.18) !important;
    border-color: rgba(248, 113, 113, 0.35) !important;
    color: #fca5a5 !important;
    -webkit-text-fill-color: #fca5a5 !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-mark-all-btn,
  html[data-pms-theme="dark"] #company-notification-center .notif-clear-all-btn,
  html[data-pms-theme="dark"] #company-notification-center .btn-pill-action {
    background: #141b3d !important;
    border-color: rgba(238, 241, 251, 0.16) !important;
    color: #eef1fb !important;
    -webkit-text-fill-color: #eef1fb !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-select {
    background-color: #141b3d !important;
    border-color: rgba(238, 241, 251, 0.16) !important;
    color: #eef1fb !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-select option {
    background-color: #141b3d !important;
    color: #eef1fb !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-filter-btn {
    background: #2563eb !important;
    border-color: #2563eb !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-badge-category {
    background: #1a2247 !important;
    border-color: rgba(238, 241, 251, 0.12) !important;
    color: #cbd5e1 !important;
    -webkit-text-fill-color: #cbd5e1 !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-sub-box,
  html[data-pms-theme="dark"] #company-notification-center .notif-inspect-box {
    background: #141b3d !important;
    border-color: rgba(238, 241, 251, 0.12) !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-sub-val-plan {
    color: #60a5fa !important;
    -webkit-text-fill-color: #60a5fa !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-delete-selected-btn {
    background: rgba(239, 68, 68, 0.16) !important;
    border-color: rgba(248, 113, 113, 0.35) !important;
    color: #fca5a5 !important;
    -webkit-text-fill-color: #fca5a5 !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-delete-selected-btn i,
  html[data-pms-theme="dark"] #company-notification-center .notif-btn-delete i {
    color: #f87171 !important;
    -webkit-text-fill-color: #f87171 !important;
  }

  html[data-pms-theme="dark"] #company-notification-center .notif-select-all-label {
    color: #cbd5e1 !important;
  }
</style>

<div id="company-notification-center" class="container-fluid">

  <!-- Hero Header Card -->
  <div class="card notif-hero-card mb-4">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="notif-hero-icon">
          <i class="bx bx-bell"></i>
        </div>
        <div>
          <h4 class="notif-hero-title d-flex align-items-center flex-wrap gap-2">
            <span>Company Notification Center</span>
            @if(($kpis['unread'] ?? 0) > 0)
              <span class="badge notif-unread-pill">{{ $kpis['unread'] }} Unread</span>
            @endif
          </h4>
          <p class="notif-hero-subtitle">Real-time subscription renewal alerts, system notifications, and operational updates.</p>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2 flex-wrap">
        <form id="deleteSelectedNotificationsForm" method="POST" action="{{ request()->routeIs('admin.company-notifications.*') && Route::has('admin.company-notifications.delete-selected') ? route('admin.company-notifications.delete-selected') : route('notifications.deleteSelected') }}" class="d-none" onsubmit="return confirm('Are you sure you want to delete the selected notifications?')">
          @csrf
          <div id="deleteSelectedIdsContainer"></div>
          <button type="submit" class="btn notif-delete-selected-btn">
            <i class="bx bx-trash"></i>
            <span id="deleteSelectedCountText">Delete Selected (0)</span>
          </button>
        </form>

        <form method="POST" action="{{ Route::has('notifications.readAll') ? route('notifications.readAll') : route('admin.company-notifications.read-all') }}">
          @csrf
          <button type="submit" class="btn notif-mark-all-btn">
            <i class="bx bx-check-double"></i> Mark All Read
          </button>
        </form>

        @if(Route::has('notifications.clearAll'))
          <form method="POST" action="{{ route('notifications.clearAll') }}" onsubmit="return confirm('Are you sure you want to permanently clear all notifications?')">
            @csrf
            <button type="submit" class="btn notif-clear-all-btn">
              <i class="bx bx-trash"></i> Clear All
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>

  @if(session('success'))
    <div id="notification-success-message" data-live-preserve class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 12px; background: #f0fdf4; color: #166534; border-left: 4px solid #16a34a !important;">
      <i class="bx bx-check-circle me-1.5 align-middle fs-5"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if(session('info'))
    <div id="notification-info-message" data-live-preserve class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 12px; background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6 !important;">
      <i class="bx bx-info-circle me-1.5 align-middle fs-5"></i> {{ session('info') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <!-- Executive KPI Grid -->
  <div id="notification-summary" class="notification-kpi-grid mb-4">
    <div>
      <div class="kpi-stat-card d-flex align-items-center gap-3">
        <div class="kpi-icon-box kpi-icon-total">
          <i class="bx bx-bell" aria-hidden="true"></i>
        </div>
        <div>
          <div class="kpi-number">{{ number_format($kpis['total'] ?? 0) }}</div>
          <div class="text-uppercase kpi-label">Total Notifications</div>
        </div>
      </div>
    </div>

    <div>
      <div class="kpi-stat-card d-flex align-items-center gap-3">
        <div class="kpi-icon-box kpi-icon-unread">
          <i class="bx bx-envelope" aria-hidden="true"></i>
        </div>
        <div>
          <div class="kpi-number kpi-number-unread">{{ number_format($kpis['unread'] ?? 0) }}</div>
          <div class="text-uppercase kpi-label">Unread Messages</div>
        </div>
      </div>
    </div>

    <div>
      <div class="kpi-stat-card d-flex align-items-center gap-3">
        <div class="kpi-icon-box kpi-icon-critical">
          <i class="bx bx-error-circle" aria-hidden="true"></i>
        </div>
        <div>
          <div class="kpi-number kpi-number-critical">{{ number_format($kpis['critical'] ?? 0) }}</div>
          <div class="text-uppercase kpi-label">Critical Alerts</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div id="notification-filters" class="card notif-filter-card mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ Route::has('notifications.all') ? route('notifications.all') : route('admin.company-notifications.index') }}" class="notification-filter-grid">
        <div class="notif-filter-selects">
          <select name="severity" class="form-select notif-select" onchange="this.form.submit()">
            <option value="">All Severities</option>
            <option value="INFO" {{ request('severity') === 'INFO' ? 'selected' : '' }}>INFO</option>
            <option value="SUCCESS" {{ request('severity') === 'SUCCESS' ? 'selected' : '' }}>SUCCESS</option>
            <option value="WARNING" {{ request('severity') === 'WARNING' ? 'selected' : '' }}>WARNING</option>
            <option value="CRITICAL" {{ request('severity') === 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
          </select>

          <select name="status" class="form-select notif-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="unread" {{ (request('status') === 'unread' || request('filter') === 'unread') ? 'selected' : '' }}>Unread Only</option>
            <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read Only</option>
          </select>
        </div>

        <div class="d-flex align-items-center gap-3 flex-wrap">
          @if((method_exists($notifications, 'count') ? $notifications->count() : count($notifications)) > 0)
            <label for="selectAllNotifications" class="notif-select-all-label">
              <input type="checkbox" id="selectAllNotifications" class="notif-select-checkbox">
              <span>Select All</span>
            </label>
          @endif
          <button type="submit" class="btn notif-filter-btn">Filter</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Notification Feed -->
  <div class="d-flex flex-column gap-3">
    @forelse($notifications as $notif)
      @php
        $isCentral = $notif instanceof \App\Models\Central\CentralNotification;

        if ($isCentral) {
            $notifId = $notif->id;
            $rawTitle = $notif->title;
            $rawMessage = $notif->message;
            $severityUpper = strtoupper($notif->severity ?? 'INFO');
            $categoryUpper = strtoupper($notif->related_module ?? 'SUBSCRIPTIONS');
            $isReadStatus = (bool) ($notif->is_read || $notif->read_at);
            $actionTargetUrl = $notif->action_url;
            $typeString = $notif->type;
            $createdAtObj = $notif->created_at;
        } else {
            $notifId = $notif->id;
            $dataArr = $notif->data ?? [];
            $classBasename = class_basename($notif->type ?? 'Notification');
            $cleanTypeTitle = trim(preg_replace('/([a-z])([A-Z])/', '$1 $2', str_replace('Notification', '', $classBasename)));

            $rawTitle = data_get($dataArr, 'title')
                ?? data_get($dataArr, 'subject')
                ?? data_get($dataArr, 'task_title')
                ?? data_get($dataArr, 'ticket_subject')
                ?? ($cleanTypeTitle ?: 'System Notification');

            $rawMessage = data_get($dataArr, 'message')
                ?? data_get($dataArr, 'body')
                ?? data_get($dataArr, 'description')
                ?? data_get($dataArr, 'text')
                ?? data_get($dataArr, 'reason')
                ?? '';

            $severityUpper = strtoupper(data_get($dataArr, 'severity', data_get($dataArr, 'level', 'INFO')));
            $categoryUpper = strtoupper(data_get($dataArr, 'category', data_get($dataArr, 'module', 'SYSTEM')));
            $isReadStatus = !is_null($notif->read_at);

            if ($taskId = data_get($dataArr, 'task_id')) {
                $actionTargetUrl = Route::has('tasks.show') ? route('tasks.show', $taskId) : '#';
            } elseif ($ticketId = data_get($dataArr, 'ticket_id')) {
                $actionTargetUrl = Route::has('tickets.show') ? route('tickets.show', $ticketId) : '#';
            } elseif ($projectId = data_get($dataArr, 'project_id')) {
                $actionTargetUrl = Route::has('projects.show') ? route('projects.show', $projectId) : '#';
            } else {
                $actionTargetUrl = data_get($dataArr, 'url') ?? data_get($dataArr, 'action_url');
            }
            $typeString = $classBasename;
            $createdAtObj = $notif->created_at;
        }

        // Subscriptions classification
        $isSubNotif = ($categoryUpper === 'SUBSCRIPTIONS' || $categoryUpper === 'SUBSCRIPTION' || str_starts_with($typeString ?? '', 'SUBSCRIPTION'));

        // Border & Color mapping
        $leftBorderColor = match($severityUpper) {
          'CRITICAL' => '#ef4444',
          'WARNING'  => '#f59e0b',
          'SUCCESS'  => '#10b981',
          default    => '#3b82f6'
        };

        $iconBg = match($severityUpper) {
          'CRITICAL' => '#fee2e2',
          'WARNING'  => '#fef3c7',
          'SUCCESS'  => '#dcfce7',
          default    => '#dbeafe'
        };

        $iconColor = match($severityUpper) {
          'CRITICAL' => '#ef4444',
          'WARNING'  => '#d97706',
          'SUCCESS'  => '#10b981',
          default    => '#2563eb'
        };

        $iconClass = match($severityUpper) {
          'CRITICAL' => 'bx-error-circle',
          'WARNING'  => 'bx-error',
          'SUCCESS'  => 'bx-check',
          default    => 'bx-info-circle'
        };

        $sevBadgeBg = match($severityUpper) {
          'CRITICAL' => '#fef2f2',
          'WARNING'  => '#fffbeb',
          'SUCCESS'  => '#dcfce7',
          default    => '#eff6ff'
        };

        $sevBadgeColor = match($severityUpper) {
          'CRITICAL' => '#b91c1c',
          'WARNING'  => '#b45309',
          'SUCCESS'  => '#15803d',
          default    => '#1d4ed8'
        };

        // Company Context for Subscriptions
        $companyObj = $isCentral ? ($notif->company ?? app(\App\Services\CompanyContext::class)->current()) : null;
        if ($isSubNotif && !$companyObj) {
            $companyObj = app(\App\Services\CompanyContext::class)->current();
        }

        $planName = 'FREE';
        $expiryDateFmt = 'N/A';
        $daysText = 'N/A';
        $daysColor = '#64748b';

        if ($isSubNotif && $companyObj) {
            $activeSub = $companyObj->subscriptions?->where('status', 'active')?->first();
            if ($activeSub && $activeSub->plan) {
                $planName = strtoupper($activeSub->plan->name);
            } elseif ($companyObj->highest_plan_slug) {
                $planName = strtoupper($companyObj->highest_plan_slug);
            } elseif ($companyObj->isOnTrial()) {
                $planName = '30-DAY FREE TRIAL';
            }

            $expiryDateObj = $activeSub?->ends_at ?? $companyObj->trial_ends_at;
            $expiryDateFmt = $expiryDateObj ? \Carbon\Carbon::parse($expiryDateObj)->format('d M Y') : 'N/A';

            $daysLeftNum = null;
            if ($expiryDateObj) {
                $daysLeftNum = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($expiryDateObj)->startOfDay(), false);
            } elseif (preg_match('/SUBSCRIPTION_(\d+)_DAYS/', $typeString, $matches)) {
                $daysLeftNum = (int) $matches[1];
            }

            if ($daysLeftNum !== null) {
                if ($daysLeftNum <= 0) {
                    $daysText = 'Expired';
                    $daysColor = '#dc2626';
                } elseif ($daysLeftNum === 1) {
                    $daysText = '1 Day';
                    $daysColor = '#991b1b';
                } else {
                    $daysText = "{$daysLeftNum} Days";
                    $daysColor = $daysLeftNum <= 3 ? '#dc2626' : ($daysLeftNum <= 5 ? '#d97706' : '#2563eb');
                }
            }
        }
      @endphp

      <div class="notif-card-item {{ !$isReadStatus ? 'notif-card-unread' : '' }}" style="border-left: 4px solid {{ $leftBorderColor }} !important;">
        <!-- Top Metadata & Action Control Bar -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="checkbox" class="notif-select-checkbox notif-item-checkbox" value="{{ ($isCentral ? 'central:' : 'user:') . $notifId }}" aria-label="Select notification {{ $rawTitle }}">

            <!-- Icon Circle -->
            <div style="--notif-icon-color: {{ $iconColor }}; background: {{ $iconBg }}; color: {{ $iconColor }};" class="notif-severity-icon">
              <i class="bx {{ $iconClass }}"></i>
            </div>

            <!-- Category Badge -->
            <span class="badge notif-badge-category text-uppercase">
              {{ $categoryUpper }}
            </span>

            <!-- Severity Badge -->
            <span class="badge notif-badge-severity" style="background: {{ $sevBadgeBg }} !important; color: {{ $sevBadgeColor }} !important; -webkit-text-fill-color: {{ $sevBadgeColor }} !important;">
              &bull; {{ $severityUpper }}
            </span>

            <!-- Action Status Badge -->
            @if(!$isReadStatus)
              <span class="badge notif-badge-status-unread">
                ✋ Action Required
              </span>
            @else
              <span class="badge notif-badge-status-read">
                ✓ Read
              </span>
            @endif
          </div>

          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="notif-time me-1">
              <i class="bx bx-time-five"></i>{{ $createdAtObj?->diffForHumans() }}
            </span>

            <!-- Inspect Button (Toggles collapsible text) -->
            <button type="button" class="btn btn-sm btn-pill-action notif-btn-inspect" data-bs-toggle="collapse" data-bs-target="#notifInspect-{{ $notifId }}">
              <i class="bx bx-show"></i> Inspect
            </button>

            <!-- Mark Read Button -->
            @if(!$isReadStatus)
              <form method="POST" action="{{ $isCentral ? route('admin.company-notifications.read', $notifId) : route('notifications.read', $notifId) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-pill-action notif-btn-read">
                  <i class="bx bx-check"></i> Read
                </button>
              </form>
            @endif

            <!-- Delete Button -->
            @php
              $singleDeleteRoute = ($isCentral && Route::has('admin.company-notifications.delete'))
                  ? route('admin.company-notifications.delete', $notifId)
                  : (Route::has('notifications.delete') ? route('notifications.delete', $notifId) : null);
            @endphp
            @if($singleDeleteRoute)
              <form method="POST" action="{{ $singleDeleteRoute }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this notification?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-pill-action notif-btn-delete" aria-label="Delete notification">
                  <i class="bx bx-trash"></i> Delete
                </button>
              </form>
            @endif
          </div>
        </div>

        <!-- Title -->
        <h5 class="notif-title mb-1">
          {{ $rawTitle }}
        </h5>

        <!-- Message Body Snippet -->
        @if($rawMessage)
          <p class="notif-message mb-2">
            {{ \Illuminate\Support\Str::limit(str_replace(["\n\n", "\n"], " ", $rawMessage), 180) }}
          </p>
        @endif

        <!-- Sub-card / Embedded Details Box for Subscriptions -->
        @if($isSubNotif && $companyObj)
          <div class="notif-sub-box mt-3">
            <div class="notif-sub-grid">
              <div>
                <small class="text-uppercase d-block mb-1 notif-sub-label">CURRENT PLAN</small>
                <strong class="notif-sub-val-plan">{{ $planName }}</strong>
              </div>

              <div>
                <small class="text-uppercase d-block mb-1 notif-sub-label">EXPIRY DATE</small>
                <strong class="notif-sub-val-expiry">{{ $expiryDateFmt }}</strong>
              </div>

              <div>
                <small class="text-uppercase d-block mb-1 notif-sub-label">DAYS REMAINING</small>
                <strong class="notif-sub-val-days" style="color: {{ $daysColor }}; -webkit-text-fill-color: {{ $daysColor }};">{{ $daysText }}</strong>
              </div>
            </div>
          </div>
        @endif

        <!-- Collapsible Inspection Details Box -->
        <div class="collapse mt-3" id="notifInspect-{{ $notifId }}">
          <div class="notif-inspect-box p-3 rounded-3 fs-7" style="line-height: 1.6; white-space: pre-line;">
            <div class="fw-bold mb-2 notif-title"><i class="bx bx-file me-1"></i> Notification Details:</div>
            {!! nl2br(e($rawMessage ?: 'No additional message details available.')) !!}
          </div>
        </div>
      </div>
    @empty
      <div class="card notif-empty-card border-0 shadow-sm p-5 text-center text-muted">
        <i class="bx bx-bell-off fs-1 d-block mb-2 opacity-50" style="color: #94a3b8;"></i>
        <div class="fw-bold fs-6 notif-title">No Notifications</div>
        <p class="fs-7 mb-0">Your company notification feed is currently clear.</p>
      </div>
    @endforelse
  </div>

  @if(method_exists($notifications, 'hasPages') && $notifications->hasPages())
    <div class="mt-4">
      {{ $notifications->links() }}
    </div>
  @endif

</div>

@push('js')
<script>
  (function () {
    var root = document.getElementById('company-notification-center');
    if (!root) return;

    var selectAll = document.getElementById('selectAllNotifications');
    var bulkForm = document.getElementById('deleteSelectedNotificationsForm');
    var idsContainer = document.getElementById('deleteSelectedIdsContainer');
    var countText = document.getElementById('deleteSelectedCountText');

    function getCheckboxes() {
      return Array.prototype.slice.call(root.querySelectorAll('.notif-item-checkbox'));
    }

    function syncBulkSelection() {
      var boxes = getCheckboxes();
      var selected = boxes.filter(function (cb) { return cb.checked; });

      if (selectAll) {
        selectAll.checked = boxes.length > 0 && selected.length === boxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < boxes.length;
      }

      if (bulkForm && idsContainer && countText) {
        idsContainer.innerHTML = '';
        selected.forEach(function (cb) {
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'ids[]';
          input.value = cb.value;
          idsContainer.appendChild(input);
        });
        countText.textContent = 'Delete Selected (' + selected.length + ')';
        bulkForm.classList.toggle('d-none', selected.length === 0);
      }
    }

    root.addEventListener('change', function (event) {
      if (event.target === selectAll) {
        getCheckboxes().forEach(function (cb) {
          cb.checked = selectAll.checked;
        });
        syncBulkSelection();
      } else if (event.target.classList && event.target.classList.contains('notif-item-checkbox')) {
        syncBulkSelection();
      }
    });

    syncBulkSelection();
  })();
</script>
@endpush
@endsection
