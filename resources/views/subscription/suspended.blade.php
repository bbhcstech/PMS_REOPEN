<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Restricted · {{ $company->name ?? 'Organization' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --accent-red: #ef4444;
            --accent-orange: #f59e0b;
            --accent-blue: #3b82f6;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border-soft: #334155;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .suspended-card {
            background: var(--card-bg);
            border: 1px solid var(--border-soft);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 980px;
            width: 100%;
            overflow: hidden;
            margin-top: 1rem;
        }

        /* ── Top Bar ── */
        .top-bar {
            background: rgba(15, 23, 42, 0.97);
            border-bottom: 1px solid var(--border-soft);
            padding: 0.75rem 1.25rem;
        }

        /* ── Header ── */
        .suspended-header {
            border-bottom: 1px solid var(--border-soft);
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .suspended-header.expired-bg {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.12) 0%, rgba(15, 23, 42, 0.8) 100%);
        }

        .suspended-header.manual-bg {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(15, 23, 42, 0.8) 100%);
        }

        .icon-badge {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.25rem;
        }

        .icon-badge.expired {
            background: rgba(239, 68, 68, 0.15);
            border: 2px solid var(--accent-red);
            color: var(--accent-red);
        }

        .icon-badge.manual {
            background: rgba(245, 158, 11, 0.15);
            border: 2px solid var(--accent-orange);
            color: var(--accent-orange);
        }

        .meta-pill {
            background: #334155;
            color: #cbd5e1;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* ── Tab Navigation ── */
        .section-tabs {
            display: flex;
            border-bottom: 1px solid var(--border-soft);
            background: rgba(15, 23, 42, 0.5);
        }

        .section-tab {
            flex: 1;
            padding: 0.85rem 1rem;
            text-align: center;
            cursor: pointer;
            color: var(--text-sub);
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.2s, border-bottom-color 0.2s;
            border-bottom: 3px solid transparent;
            user-select: none;
        }

        .section-tab:hover {
            color: var(--text-main);
        }

        .section-tab.active {
            color: var(--accent-blue);
            border-bottom-color: var(--accent-blue);
        }

        /* ── Sections ── */
        .section-panel {
            display: none;
            padding: 1.75rem 1.5rem;
        }

        .section-panel.active {
            display: block;
        }

        /* ── Plan Cards ── */
        .plan-card {
            background: #0f172a;
            border: 1px solid var(--border-soft);
            border-radius: 14px;
            padding: 1.5rem;
            height: 100%;
            transition: transform 0.2s ease, border-color 0.2s ease;
            position: relative;
        }

        .plan-card:hover:not(.disabled-plan) {
            transform: translateY(-4px);
            border-color: var(--accent-blue);
        }

        .plan-card.featured {
            border-color: var(--accent-blue);
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.08) 0%, #0f172a 100%);
        }

        .plan-card.disabled-plan {
            opacity: 0.42;
            cursor: not-allowed;
            filter: grayscale(60%);
        }

        .plan-card.disabled-plan .btn-upgrade {
            pointer-events: none;
            background: #334155;
            color: #64748b;
        }

        .current-plan-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
        }

        .btn-upgrade {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            transition: all 0.2s ease;
        }

        .btn-upgrade:hover {
            background: #2563eb;
            color: white;
        }

        /* ── Notifications ── */
        .notif-item {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--border-soft);
            border-radius: 12px;
            padding: 1rem 1.2rem;
            margin-bottom: 0.75rem;
            transition: border-color 0.2s;
        }

        .notif-item:hover {
            border-color: #475569;
        }

        .notif-item.unread {
            border-left: 3px solid var(--accent-blue);
        }

        .notif-severity-critical { color: #ef4444; }
        .notif-severity-warning  { color: #f59e0b; }
        .notif-severity-info     { color: #3b82f6; }
        .notif-severity-success  { color: #22c55e; }

        /* ── Manual Suspension Lock Box ── */
        .manual-lock-box {
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.35);
            border-radius: 14px;
            padding: 1.5rem;
            text-align: center;
        }

        /* ── Footer Bar ── */
        .footer-bar {
            border-top: 1px solid var(--border-soft);
            padding: 1rem 1.5rem;
            background: rgba(15, 23, 42, 0.6);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
    </style>
</head>
<body>

@php
    $isManualSuspension = (bool) ($company->manually_suspended ?? false);
    $statusLabel = $isManualSuspension ? 'SUSPENDED BY ADMIN' : 'EXPIRED';
    $statusColor = $isManualSuspension ? 'warning' : 'danger';
    $iconClass   = $isManualSuspension ? 'manual' : 'expired';
    $headerClass = $isManualSuspension ? 'manual-bg' : 'expired-bg';

    // Load notifications inline
    try {
        $companyId = $company->id ?? null;
        $companyNotifications = ($companyId && in_array(strtolower((string) auth()->user()?->role), ['admin', 'administrator', 'superadmin'], true))
            ? \App\Models\Central\CentralNotification::on('central')
                ->where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('target_audience', 'company_admin')
                      ->orWhere('target_audience', 'all');
                })
                ->orderByRaw('is_read ASC')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
            : collect();
    } catch (\Throwable $e) {
        $companyNotifications = collect();
    }

    // Load allowed plans (only for non-manual suspension)
    $allowedPlans = collect();
    $highestLevel = 0;
    if (!$isManualSuspension) {
        try {
            $eligService  = app(\App\Services\PlanEligibilityService::class);
            $allowedPlans = $eligService->getAllowedPlans($company);
            $highestLevel = \App\Services\PlanEligibilityService::getHighestLevel($company);
        } catch (\Throwable $e) {}
    }

    $unreadCount = $companyNotifications->where('is_read', false)->count();
@endphp

<div class="suspended-card">

    {{-- ── Top Bar ── --}}
    <div class="top-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="fw-bold text-white fs-6">
            <i class="bx bx-shield-quarter text-{{ $statusColor }} me-2"></i>
            {{ $company->name ?? 'Organization' }} — Access Restricted
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">
                    <i class="bx bx-log-out me-1"></i> Log Out
                </button>
            </form>
        </div>
    </div>

    {{-- ── Header ── --}}
    <div class="suspended-header {{ $headerClass }}">
        <div class="icon-badge {{ $iconClass }}">
            <i class="bx {{ $isManualSuspension ? 'bx-lock-alt' : 'bx-error-alt' }}"></i>
        </div>
        <h2 class="fw-bold mb-2">
            {{ $isManualSuspension ? 'Account Suspended by Administrator' : 'Subscription Expired' }}
        </h2>
        <p class="mb-3" style="max-width: 620px; margin: 0 auto; color: #94a3b8;">
            @if($isManualSuspension)
                Your organization <strong>{{ $company->name ?? 'Organization' }}</strong> has been <strong>manually suspended</strong> by the platform Super Admin.
                @if($company->suspension_reason)
                <span class="d-block mt-3"><strong>{{ $company->suspension_category }}</strong><br>{{ $company->suspension_reason }}</span>
                @endif
                Access can only be restored by the Super Admin. Please contact your platform administrator.
            @else
                Your organization <strong>{{ $company->name ?? 'Organization' }}</strong> has been temporarily restricted because your subscription or Free Trial period has ended.
                Select a plan below to instantly restore access.
            @endif
        </p>

        <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
            <span class="meta-pill"><i class="bx bx-building me-1"></i> {{ $company->name ?? 'Organization' }}</span>
            <span class="meta-pill"><i class="bx bx-award me-1"></i> Previous Plan: {{ strtoupper($company->highest_plan_slug ?: 'FREE') }}</span>
            <span class="meta-pill bg-{{ $statusColor }} text-white">
                <i class="bx {{ $isManualSuspension ? 'bx-lock-alt' : 'bx-block' }} me-1"></i> Status: {{ $statusLabel }}
            </span>
        </div>
    </div>

    {{-- ── Tab Navigation ── --}}
    <div class="section-tabs">
        <div class="section-tab active" id="tab-plans" onclick="switchTab('plans')">
            <i class="bx bxs-zap me-1"></i>
            @if($isManualSuspension) Status &amp; Info @else Plans &amp; Renewal @endif
        </div>
        <div class="section-tab" id="tab-notifications" onclick="switchTab('notifications')">
            <i class="bx bx-bell me-1"></i>
            Notifications
            @if($unreadCount > 0)
                <span class="badge bg-danger ms-1" style="font-size:0.7rem;">{{ $unreadCount }}</span>
            @endif
        </div>
    </div>

    {{-- ── Plans / Renewal Panel ── --}}
    <div class="section-panel active" id="panel-plans">

        @if($isManualSuspension)
            {{-- Manual suspension — cannot self-renew --}}
            <div class="manual-lock-box my-3">
                <i class="bx bx-lock-alt fs-1 text-warning mb-3"></i>
                <h5 class="fw-bold text-warning mb-2">Access Locked by Super Admin</h5>
                <p style="color: #94a3b8; max-width: 520px; margin: 0 auto 1.25rem;">
                    Your organization has been suspended by the platform Super Admin.
                    This is not an expired subscription — renewing or changing your plan will <strong class="text-warning">not</strong> restore access.
                    Only the Super Admin can lift this suspension.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="mailto:support@pms.com" class="btn btn-outline-warning">
                        <i class="bx bx-envelope me-1"></i> Contact Super Admin
                    </a>
                </div>
            </div>

            {{-- Show plans for reference only (all disabled) --}}
            @php
                try {
                    $allPlans = \App\Models\Central\Plan::on('central')->orderBy('monthly_price')->get();
                } catch (\Throwable $e) {
                    $allPlans = collect();
                }
            @endphp

            @if($allPlans->isNotEmpty())
                <h6 class="fw-bold text-sub text-center mt-4 mb-3" style="font-size:0.8rem; letter-spacing: 0.06em; text-transform:uppercase;">
                    Plan Reference (Locked — Contact Admin to Restore Access)
                </h6>
                <div class="row g-3 mb-2">
                    @foreach($allPlans as $plan)
                        <div class="col-md-4">
                            <div class="plan-card disabled-plan">
                                <h5 class="fw-bold mb-0 text-white">{{ $plan->name }}</h5>
                                <div class="mt-2 mb-3">
                                    <span class="fs-3 fw-bold text-white">₹{{ number_format($plan->monthly_price, 0) }}</span>
                                    <span style="color:#64748b;">/ month</span>
                                </div>
                                <button class="btn btn-upgrade" disabled>
                                    <i class="bx bx-lock-alt me-1"></i> Locked
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        @else
            {{-- Auto-expired — can renew/upgrade --}}
            <div class="alert alert-warning border-warning bg-dark text-warning mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-shield-quarter fs-3 me-3"></i>
                    <div>
                        <strong>Data Preservation Guarantee:</strong>
                        All your organization's projects, employee records, tasks, files, and settings remain 100% safe and intact.
                        Select a paid plan below to instantly restore access.
                    </div>
                </div>
            </div>

            <h4 class="fw-bold text-center mb-4">
                <i class="bx bx-rocket me-2 text-primary"></i>Choose a Plan to Restore Access
            </h4>

            <div class="row g-4 mb-2">
                @forelse($allowedPlans as $plan)
                    @php
                        $planLevel = \App\Services\PlanEligibilityService::getPlanLevel($plan);
                        $isDowngrade = $planLevel < $highestLevel;
                        $isCurrent = $planLevel === $highestLevel;
                    @endphp
                    <div class="col-md-4">
                        <div class="plan-card {{ $loop->first && !$isDowngrade ? 'featured' : '' }} {{ $isDowngrade ? 'disabled-plan' : '' }}">

                            @if($isCurrent)
                                <span class="badge bg-secondary current-plan-badge">Current Plan</span>
                            @elseif($loop->first && !$isDowngrade)
                                <span class="badge bg-primary current-plan-badge">Recommended</span>
                            @endif

                            @if($isDowngrade)
                                <span class="badge bg-danger current-plan-badge">Downgrade Not Allowed</span>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold mb-0 text-white">{{ $plan->name }}</h5>
                            </div>
                            <p class="small mb-3" style="color: var(--text-sub);">{{ $plan->description }}</p>

                            <div class="mb-4">
                                <span class="fs-2 fw-bold text-white">₹{{ number_format($plan->monthly_price, 0) }}</span>
                                <span style="color: var(--text-sub);">/ month</span>
                            </div>

                            <ul class="list-unstyled small mb-4" style="color: var(--text-sub);">
                                <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Up to {{ $plan->max_users == 0 ? 'Unlimited' : $plan->max_users }} Employees</li>
                                <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Up to {{ $plan->max_projects == 0 ? 'Unlimited' : $plan->max_projects }} Active Projects</li>
                                <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Up to {{ $plan->max_clients == 0 ? 'Unlimited' : $plan->max_clients }} Clients</li>
                                <li class="mb-2"><i class="bx bx-check text-success me-2"></i>{{ $plan->max_storage_mb >= 1024 ? ($plan->max_storage_mb / 1024) . ' GB' : $plan->max_storage_mb . ' MB' }} Storage</li>
                            </ul>

                            @if($isDowngrade)
                                <button class="btn btn-upgrade" disabled title="Downgrade not allowed">
                                    <i class="bx bx-block me-1"></i> Downgrade Unavailable
                                </button>
                            @else
                                <form action="{{ Route::has('super-admin.subscriptions.store') ? route('super-admin.subscriptions.store') : (Route::has('super-admin.subscriptions.assign') ? route('super-admin.subscriptions.assign') : (Route::has('superadmin.subscriptions.store') ? route('superadmin.subscriptions.store') : (Route::has('subscriptions.store') ? route('subscriptions.store') : route('subscriptions.assign')))) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <input type="hidden" name="billing_cycle" value="monthly">
                                    <button type="submit" class="btn btn-upgrade">
                                        <i class="bx bxs-zap me-1"></i>
                                        {{ $isCurrent ? 'Renew ' : 'Activate ' }}{{ $plan->name }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4" style="color: var(--text-sub);">
                        No eligible plans found. Please contact your platform Super Admin.
                    </div>
                @endforelse
            </div>
        @endif

    </div>

    {{-- ── Notifications Panel ── --}}
    <div class="section-panel" id="panel-notifications">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bx bx-bell me-2 text-primary"></i>Notifications</h5>
            @if($companyNotifications->isNotEmpty())
                <form action="{{ route('admin.company-notifications.read-all') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-check-double me-1"></i>Mark All Read
                    </button>
                </form>
            @endif
        </div>

        @forelse($companyNotifications as $notif)
            @php
                $severityIcon = match(strtolower($notif->severity ?? 'info')) {
                    'critical' => 'bx-error-circle notif-severity-critical',
                    'warning'  => 'bx-error notif-severity-warning',
                    'success'  => 'bx-check-circle notif-severity-success',
                    default    => 'bx-info-circle notif-severity-info',
                };
            @endphp
            <div class="notif-item {{ $notif->is_read ? '' : 'unread' }}">
                <div class="d-flex align-items-start gap-3">
                    <i class="bx {{ $severityIcon }} fs-4 mt-1" style="flex-shrink:0;"></i>
                    <div style="flex:1; min-width:0;">
                        <div class="fw-semibold mb-1">{{ $notif->title ?? 'Notification' }}</div>
                        <div class="small" style="color: var(--text-sub); white-space: pre-wrap;">{{ $notif->message }}</div>
                        <div class="small mt-2" style="color: #475569;">
                            {{ $notif->created_at?->diffForHumans() ?? '' }}
                            @if(!$notif->is_read)
                                &nbsp;·&nbsp;
                                <form action="{{ route('admin.company-notifications.read', $notif->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm p-0" style="color: #3b82f6; font-size:0.78rem;">
                                        Mark read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5" style="color: var(--text-sub);">
                <i class="bx bx-bell-off fs-1 mb-3 d-block"></i>
                No notifications yet.
            </div>
        @endforelse

    </div>

    {{-- ── Footer ── --}}
    <div class="footer-bar">
        <div style="color: var(--text-sub); font-size: 0.85rem;">
            @if($isManualSuspension)
                <i class="bx bx-info-circle me-1"></i>
                Only the Super Admin can restore access to this organization.
            @else
                <i class="bx bx-info-circle me-1"></i>
                All data is preserved. Renew to instantly restore access.
            @endif
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="bx bx-log-out me-1"></i> Log Out
                </button>
            </form>
        </div>
    </div>

</div>

<script>
function switchTab(tab) {
    // Hide all panels
    document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.section-tab').forEach(t => t.classList.remove('active'));

    // Show selected
    document.getElementById('panel-' + tab).classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}
</script>
</body>
</html>
