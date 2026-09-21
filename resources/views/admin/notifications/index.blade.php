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
  .notif-card-item {
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .notif-card-item:hover {
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.07);
    border-color: #cbd5e1;
    transform: translateY(-1px);
  }
  .notif-card-unread {
    background: #f8fafc;
  }
  .kpi-stat-card {
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
    transition: all 0.25s ease;
  }
  .kpi-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
  }
  .btn-pill-action {
    font-size: 12px;
    font-weight: 700;
    border-radius: 50px;
    padding: 5px 14px;
    transition: all 0.2s ease;
  }
  .btn-pill-action:hover {
    transform: scale(1.03);
  }

  /* Filter Toolbar & Controls */
  .notif-filter-card {
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
  }
  .notif-select {
    border-radius: 10px;
    font-weight: 500;
    font-size: 13px;
    background-color: #f8fafc;
    color: #1e293b;
    border: 1px solid #e2e8f0;
  }
  .notif-filter-btn {
    border-radius: 10px;
    font-size: 13px;
  }

  /* Subscription Embedded Card */
  .notif-sub-box {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
  }
  .notif-sub-divider {
    height: 28px;
    width: 1px;
    background: #cbd5e1;
  }
  .notif-sub-label {
    color: #64748b;
  }
  .notif-sub-val-plan {
    color: #2563eb;
  }
  .notif-sub-val-expiry {
    color: #0f172a;
  }
  .notif-inspect-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #1e293b;
  }
  .notif-empty-card {
    border-radius: 20px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
  }

  /* KPI Icons Light Defaults */
  .kpi-icon-total {
    background: #eff6ff;
    color: #2563eb;
  }
  .kpi-icon-unread {
    background: #fffbeb;
    color: #d97706;
  }
  .kpi-icon-critical {
    background: #fef2f2;
    color: #ef4444;
  }
  .kpi-number-unread {
    color: #d97706;
  }
  .kpi-number-critical {
    color: #ef4444;
  }

  /* --------------------------------------------------------------------------
     DARK MODE VISIBILITY RULES FOR COMPANY NOTIFICATION CENTER
     -------------------------------------------------------------------------- */
  html[data-pms-theme="dark"] .kpi-stat-card {
    background: #0F1530 !important;
    border: 1px solid rgba(238, 241, 251, 0.09) !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.4) !important;
  }
  html[data-pms-theme="dark"] .kpi-stat-card:hover {
    border-color: rgba(47, 107, 255, 0.4) !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.55) !important;
  }
  html[data-pms-theme="dark"] .kpi-stat-card .kpi-icon-total {
    background: rgba(47, 107, 255, 0.18) !important;
    color: #60A5FA !important;
  }
  html[data-pms-theme="dark"] .kpi-stat-card .kpi-icon-unread {
    background: rgba(245, 158, 11, 0.18) !important;
    color: #FCD34D !important;
  }
  html[data-pms-theme="dark"] .kpi-stat-card .kpi-icon-critical {
    background: rgba(239, 68, 68, 0.18) !important;
    color: #FCA5A5 !important;
  }
  html[data-pms-theme="dark"] .kpi-stat-card .kpi-number,
  html[data-pms-theme="dark"] .kpi-stat-card .fs-4 {
    color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .kpi-stat-card .kpi-number-unread {
    color: #FCD34D !important;
  }
  html[data-pms-theme="dark"] .kpi-stat-card .kpi-number-critical,
  html[data-pms-theme="dark"] .kpi-stat-card .text-danger {
    color: #FCA5A5 !important;
  }
  html[data-pms-theme="dark"] .kpi-stat-card .kpi-label,
  html[data-pms-theme="dark"] .kpi-stat-card .text-muted {
    color: #9AA3C7 !important;
  }

  /* Filter Card & Dropdown Selects */
  html[data-pms-theme="dark"] .notif-filter-card {
    background: #0F1530 !important;
    border: 1px solid rgba(238, 241, 251, 0.09) !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.4) !important;
  }
  html[data-pms-theme="dark"] .notif-select,
  html[data-pms-theme="dark"] .notif-filter-card select,
  html[data-pms-theme="dark"] select.notif-select {
    background-color: #141B3D !important;
    color: #EEF1FB !important;
    border: 1px solid rgba(238, 241, 251, 0.18) !important;
  }
  html[data-pms-theme="dark"] .notif-select:focus,
  html[data-pms-theme="dark"] .notif-filter-card select:focus {
    border-color: #2F6BFF !important;
    box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.28) !important;
  }
  html[data-pms-theme="dark"] .notif-select option,
  html[data-pms-theme="dark"] .notif-filter-card select option,
  html[data-pms-theme="dark"] select option {
    background-color: #141B3D !important;
    color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .notif-filter-btn {
    background: #2F6BFF !important;
    border-color: #2F6BFF !important;
    color: #FFFFFF !important;
  }
  html[data-pms-theme="dark"] .notif-filter-btn:hover {
    background: #1E4FCC !important;
    border-color: #1E4FCC !important;
    box-shadow: 0 4px 12px rgba(47, 107, 255, 0.35) !important;
  }

  /* Notification Items */
  html[data-pms-theme="dark"] .notif-card-item {
    background: #0F1530 !important;
    border: 1px solid rgba(238, 241, 251, 0.09) !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35) !important;
    color: #CBD5E1 !important;
  }
  html[data-pms-theme="dark"] .notif-card-item:hover {
    border-color: rgba(47, 107, 255, 0.35) !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5) !important;
  }
  html[data-pms-theme="dark"] .notif-card-unread {
    background: #141B3D !important;
    border-color: rgba(47, 107, 255, 0.3) !important;
  }
  html[data-pms-theme="dark"] .notif-card-item .notif-title,
  html[data-pms-theme="dark"] .notif-card-item h5.text-dark {
    color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .notif-card-item .notif-message,
  html[data-pms-theme="dark"] .notif-card-item p.text-secondary {
    color: #CBD5E1 !important;
  }
  html[data-pms-theme="dark"] .notif-card-item .notif-badge-category {
    background: #1A2247 !important;
    color: #9AA3C7 !important;
    border: 1px solid rgba(238, 241, 251, 0.12) !important;
  }
  html[data-pms-theme="dark"] .notif-card-item .notif-time,
  html[data-pms-theme="dark"] .notif-card-item .text-muted {
    color: #9AA3C7 !important;
  }
  html[data-pms-theme="dark"] .notif-card-item .notif-time i {
    color: #60A5FA !important;
  }

  /* Action Buttons */
  html[data-pms-theme="dark"] .notif-btn-inspect,
  html[data-pms-theme="dark"] .btn-pill-action.btn-outline-secondary {
    background: #141B3D !important;
    border: 1px solid rgba(238, 241, 251, 0.2) !important;
    color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .notif-btn-inspect:hover,
  html[data-pms-theme="dark"] .btn-pill-action.btn-outline-secondary:hover {
    background: #1E295A !important;
    border-color: #2F6BFF !important;
    color: #FFFFFF !important;
  }
  html[data-pms-theme="dark"] .notif-btn-read,
  html[data-pms-theme="dark"] .btn-pill-action.btn-outline-dark {
    background: #141B3D !important;
    border: 1px solid rgba(238, 241, 251, 0.22) !important;
    color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .notif-btn-read:hover,
  html[data-pms-theme="dark"] .btn-pill-action.btn-outline-dark:hover {
    background: #1E295A !important;
    border-color: #2F6BFF !important;
    color: #60A5FA !important;
  }
  html[data-pms-theme="dark"] .notif-btn-resolve,
  html[data-pms-theme="dark"] .btn-pill-action.btn-outline-success {
    background: rgba(16, 185, 129, 0.16) !important;
    border: 1px solid rgba(16, 185, 129, 0.45) !important;
    color: #6EE7B7 !important;
  }
  html[data-pms-theme="dark"] .notif-btn-resolve:hover,
  html[data-pms-theme="dark"] .btn-pill-action.btn-outline-success:hover {
    background: #10B981 !important;
    border-color: #10B981 !important;
    color: #FFFFFF !important;
  }

  /* Embedded Subscription Box */
  html[data-pms-theme="dark"] .notif-sub-box {
    background: #141B3D !important;
    border: 1px solid rgba(238, 241, 251, 0.12) !important;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3) !important;
  }
  html[data-pms-theme="dark"] .notif-sub-label {
    color: #9AA3C7 !important;
  }
  html[data-pms-theme="dark"] .notif-sub-val-plan {
    color: #60A5FA !important;
  }
  html[data-pms-theme="dark"] .notif-sub-val-expiry {
    color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .notif-sub-divider {
    background: rgba(238, 241, 251, 0.15) !important;
  }

  /* Inspection Details & Empty State */
  html[data-pms-theme="dark"] .notif-inspect-box {
    background: #141B3D !important;
    border: 1px solid rgba(238, 241, 251, 0.12) !important;
    color: #CBD5E1 !important;
  }
  html[data-pms-theme="dark"] .notif-inspect-box .notif-title,
  html[data-pms-theme="dark"] .notif-inspect-box .text-dark {
    color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .notif-empty-card {
    background: #0F1530 !important;
    border: 1px solid rgba(238, 241, 251, 0.09) !important;
    color: #9AA3C7 !important;
  }
  html[data-pms-theme="dark"] .notif-empty-card .notif-title,
  html[data-pms-theme="dark"] .notif-empty-card .text-dark {
    color: #EEF1FB !important;
  }

  /* Mark All Read Button */
  html[data-pms-theme="dark"] .notif-mark-all-btn {
    background: #141B3D !important;
    border: 1px solid rgba(238, 241, 251, 0.2) !important;
    color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .notif-mark-all-btn:hover {
    background: #1A2247 !important;
    border-color: #2F6BFF !important;
    color: #60A5FA !important;
  }
</style>

<div class="container-fluid px-4 py-4" style="max-width: 1140px;">

  <!-- Hero Header Banner -->
  <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1e293b 100%); border-radius: 20px; color: #fff; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #60a5fa; border: 1px solid rgba(255, 255, 255, 0.15);">
          <i class="bx bx-bell"></i>
        </div>
        <div>
          <h4 class="fw-bold mb-1 text-white d-flex align-items-center gap-2">
            <span>Company Notification Center</span>
            @if($kpis['unread'] > 0)
              <span class="badge bg-danger rounded-pill fs-8 px-2.5 py-1">{{ $kpis['unread'] }} Unread</span>
            @endif
          </h4>
          <p class="mb-0 text-white-50 fs-7">Real-time subscription renewal alerts, system notifications, and operational updates.</p>
        </div>
      </div>

      <div>
        <form method="POST" action="{{ Route::has('notifications.readAll') ? route('notifications.readAll') : route('admin.company-notifications.read-all') }}">
          @csrf
          <button type="submit" class="btn btn-light notif-mark-all-btn fw-bold px-3.5 py-2.5 shadow-sm d-flex align-items-center gap-1.5" style="border-radius: 10px; font-size: 13px;">
            <i class="bx bx-check-double fs-5"></i> Mark All Read
          </button>
        </form>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 12px; background: #f0fdf4; color: #166534; border-left: 4px solid #16a34a !important;">
      <i class="bx bx-check-circle me-1.5 align-middle fs-5"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <!-- Executive KPI Grid -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="kpi-stat-card p-3 d-flex align-items-center gap-3">
        <div class="kpi-icon-box kpi-icon-total p-3 rounded-4 fs-3">
          <i class="bx bx-bell-ring"></i>
        </div>
        <div>
          <div class="fs-4 fw-bolder kpi-number" style="letter-spacing: -0.5px;">{{ number_format($kpis['total']) }}</div>
          <div class="fs-8 text-muted text-uppercase fw-bold kpi-label" style="letter-spacing: 0.5px;">Total Notifications</div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="kpi-stat-card p-3 d-flex align-items-center gap-3">
        <div class="kpi-icon-box kpi-icon-unread p-3 rounded-4 fs-3">
          <i class="bx bx-envelope"></i>
        </div>
        <div>
          <div class="fs-4 fw-bolder kpi-number kpi-number-unread" style="letter-spacing: -0.5px;">{{ number_format($kpis['unread']) }}</div>
          <div class="fs-8 text-muted text-uppercase fw-bold kpi-label" style="letter-spacing: 0.5px;">Unread Messages</div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="kpi-stat-card p-3 d-flex align-items-center gap-3">
        <div class="kpi-icon-box kpi-icon-critical p-3 rounded-4 fs-3">
          <i class="bx bx-error-alt"></i>
        </div>
        <div>
          <div class="fs-4 fw-bolder text-danger kpi-number kpi-number-critical" style="letter-spacing: -0.5px;">{{ number_format($kpis['critical']) }}</div>
          <div class="fs-8 text-muted text-uppercase fw-bold kpi-label" style="letter-spacing: 0.5px;">Critical Alerts</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card notif-filter-card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ Route::has('notifications.all') ? route('notifications.all') : route('admin.company-notifications.index') }}" class="row g-2 align-items-center">
        <div class="col-md-5">
          <select name="severity" class="form-select notif-select border-0" onchange="this.form.submit()">
            <option value="">All Severities</option>
            <option value="INFO" {{ request('severity') === 'INFO' ? 'selected' : '' }}>INFO</option>
            <option value="SUCCESS" {{ request('severity') === 'SUCCESS' ? 'selected' : '' }}>SUCCESS</option>
            <option value="WARNING" {{ request('severity') === 'WARNING' ? 'selected' : '' }}>WARNING</option>
            <option value="CRITICAL" {{ request('severity') === 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
          </select>
        </div>

        <div class="col-md-5">
          <select name="status" class="form-select notif-select border-0" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread Only</option>
            <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read Only</option>
          </select>
        </div>

        <div class="col-md-2 text-end">
          <button type="submit" class="btn btn-dark notif-filter-btn w-100 fw-bold shadow-2xs">Filter</button>
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
          'SUCCESS'  => '#d1fae5',
          default    => '#dbeafe'
        };

        $iconColor = match($severityUpper) {
          'CRITICAL' => '#ef4444',
          'WARNING'  => '#d97706',
          'SUCCESS'  => '#059669',
          default    => '#2563eb'
        };

        $iconClass = match($severityUpper) {
          'CRITICAL' => 'bx-error-circle',
          'WARNING'  => 'bx-error',
          'SUCCESS'  => 'bx-check-circle',
          default    => 'bx-info-circle'
        };

        $sevBadgeBg = match($severityUpper) {
          'CRITICAL' => '#fef2f2',
          'WARNING'  => '#fffbeb',
          'SUCCESS'  => '#f0fdf4',
          default    => '#eff6ff'
        };

        $sevBadgeColor = match($severityUpper) {
          'CRITICAL' => '#ef4444',
          'WARNING'  => '#d97706',
          'SUCCESS'  => '#16a34a',
          default    => '#2563eb'
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

      <div class="notif-card-item p-4 {{ !$isReadStatus ? 'notif-card-unread' : '' }}" style="border-left: 5px solid {{ $leftBorderColor }} !important;">
        <!-- Top Metadata & Action Control Bar -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Icon Circle -->
            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $iconBg }}; color: {{ $iconColor }}; display: flex; align-items: center; justify-content: center; font-size: 16px;" class="flex-shrink-0">
              <i class="bx {{ $iconClass }}"></i>
            </div>

            <!-- Category Badge -->
            <span class="badge notif-badge-category px-2.5 py-1.5 text-uppercase" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 11px; letter-spacing: 0.5px;">
              {{ $categoryUpper }}
            </span>

            <!-- Severity Badge -->
            <span class="badge px-2.5 py-1.5" style="background: {{ $sevBadgeBg }}; color: {{ $sevBadgeColor }}; font-weight: 700; font-size: 11px;">
              • {{ $severityUpper }}
            </span>

            <!-- Action Status Badge -->
            @if(!$isReadStatus)
              <span class="badge px-2.5 py-1.5" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-weight: 700; font-size: 11px;">
                ✋ Action Required
              </span>
            @else
              <span class="badge px-2.5 py-1.5" style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; font-weight: 700; font-size: 11px;">
                ✓ Read
              </span>
            @endif
          </div>

          <div class="d-flex align-items-center gap-2">
            <span class="notif-time me-2" style="font-size: 12.5px;">
              <i class="bx bx-time-five me-1"></i>{{ $createdAtObj?->diffForHumans() }}
            </span>

            <!-- Inspect Button (Toggles collapsible text) -->
            <button type="button" class="btn btn-sm btn-outline-secondary btn-pill-action notif-btn-inspect" data-bs-toggle="collapse" data-bs-target="#notifInspect-{{ $notifId }}">
              <i class="bx bx-show me-1"></i> Inspect
            </button>

            <!-- Mark Read Button -->
            @if(!$isReadStatus)
              <form method="POST" action="{{ $isCentral ? route('admin.company-notifications.read', $notifId) : route('notifications.read', $notifId) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-dark btn-pill-action notif-btn-read">
                  <i class="bx bx-check me-1"></i> Read
                </button>
              </form>
            @endif

            <!-- Resolve / Open Resource Button -->
            @if($actionTargetUrl && $actionTargetUrl !== '#')
              <a href="{{ $actionTargetUrl }}" class="btn btn-sm btn-outline-success btn-pill-action notif-btn-resolve">
                <i class="bx bx-check-circle me-1"></i> Resolve
              </a>
            @endif
          </div>
        </div>

        <!-- Title -->
        <h5 class="fw-bold notif-title mb-1" style="font-size: 16px; letter-spacing: -0.2px;">
          {{ $rawTitle }}
        </h5>

        <!-- Message Body Snippet -->
        @if($rawMessage)
          <p class="notif-message mb-2" style="font-size: 13.5px; line-height: 1.5;">
            {{ \Illuminate\Support\Str::limit(str_replace(["\n\n", "\n"], " ", $rawMessage), 180) }}
          </p>
        @endif

        <!-- Sub-card / Embedded Details Box for Subscriptions -->
        @if($isSubNotif && $companyObj)
          <div class="notif-sub-box p-3 rounded-4 mt-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div class="d-flex align-items-center gap-4 flex-wrap">
                <div>
                  <small class="text-uppercase fw-bold notif-sub-label d-block mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">CURRENT PLAN</small>
                  <strong class="fs-6 notif-sub-val-plan">{{ $planName }}</strong>
                </div>

                <div class="notif-sub-divider d-none d-sm-block"></div>

                <div>
                  <small class="text-uppercase fw-bold notif-sub-label d-block mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">EXPIRY DATE</small>
                  <strong class="fs-6 notif-sub-val-expiry">{{ $expiryDateFmt }}</strong>
                </div>

                <div class="notif-sub-divider d-none d-sm-block"></div>

                <div>
                  <small class="text-uppercase fw-bold notif-sub-label d-block mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">DAYS REMAINING</small>
                  <strong class="fs-6 notif-sub-val-days" style="color: {{ $daysColor }};">{{ $daysText }}</strong>
                </div>
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
@endsection
