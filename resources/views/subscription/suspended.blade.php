<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended · Subscription Expired</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --accent-red: #ef4444;
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
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .suspended-card {
            background: var(--card-bg);
            border: 1px solid var(--border-soft);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 960px;
            width: 100%;
            overflow: hidden;
        }

        .suspended-header {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(15, 23, 42, 0.8) 100%);
            border-bottom: 1px solid var(--border-soft);
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .icon-badge {
            width: 80px;
            height: 80px;
            background: rgba(239, 68, 68, 0.15);
            border: 2px solid var(--accent-red);
            color: var(--accent-red);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.25rem;
        }

        .plan-card {
            background: #0f172a;
            border: 1px solid var(--border-soft);
            border-radius: 14px;
            padding: 1.5rem;
            height: 100%;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .plan-card:hover {
            transform: translateY(-4px);
            border-color: var(--accent-blue);
        }

        .plan-card.featured {
            border-color: var(--accent-blue);
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.08) 0%, #0f172a 100%);
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

        .meta-pill {
            background: #334155;
            color: #cbd5e1;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .contact-box {
            background: rgba(51, 65, 85, 0.3);
            border: 1px dashed var(--border-soft);
            border-radius: 12px;
            padding: 1.25rem;
        }
    </style>
</head>
<body>

<div class="suspended-card">
    <div class="d-flex justify-content-between align-items-center p-3 px-4 border-bottom" style="background: rgba(15, 23, 42, 0.95); border-color: var(--border-soft) !important;">
        <div class="fw-bold text-white fs-6">
            <i class="bx bx-shield-quarter text-danger me-2"></i>{{ $company->name ?? 'Organization' }} Access Restricted
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('notifications.all') }}" class="btn btn-sm btn-outline-primary fw-bold">
                <i class="bx bx-bell me-1"></i> Notifications
            </a>
            <a href="{{ route('subscription.suspended') }}" class="btn btn-sm btn-primary fw-bold">
                <i class="bx bx-zap me-1"></i> Plans &amp; Renewal
            </a>
            <a href="{{ route('admin.company-complaints.index') }}" class="btn btn-sm btn-outline-info fw-bold">
                <i class="bx bx-headphone me-1"></i> Support
            </a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline ms-1">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light"><i class="bx bx-log-out me-1"></i> Log Out</button>
            </form>
        </div>
    </div>

    <div class="suspended-header">
        <div class="icon-badge">
            <i class="bx bx-error-alt"></i>
        </div>
        <h2 class="fw-bold mb-2">Subscription Expired / Account Suspended</h2>
        <p class="text-sub mb-3" style="max-width: 600px; margin: 0 auto; color: #94a3b8;">
            Your organization <strong>{{ $company->name ?? 'Organization' }}</strong> has been temporarily suspended because your subscription or 30-day Free Trial period has ended.
        </p>

        <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
            <span class="meta-pill"><i class="bx bx-building me-1"></i> {{ $company->name ?? 'Organization' }}</span>
            <span class="meta-pill"><i class="bx bx-award me-1"></i> Previous Plan: {{ strtoupper($company->highest_plan_slug ?: 'FREE') }}</span>
            <span class="meta-pill bg-danger text-white"><i class="bx bx-block me-1"></i> Status: SUSPENDED</span>
        </div>
    </div>

    <div class="p-4 p-md-5">
        <div class="alert alert-warning border-warning bg-dark text-warning mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-shield-quarter fs-3 me-3"></i>
                <div>
                    <strong>Data Preservation Guarantee:</strong> All your organization's projects, employee records, tasks, files, and settings remain 100% safe and intact. Select a paid plan below to instantly restore access.
                </div>
            </div>
        </div>

        <h4 class="fw-bold text-center mb-4"><i class="bx bx-rocket me-2 text-primary"></i>Choose a Paid Plan to Restore Access</h4>

        @php
            $allowedPlans = app(\App\Services\PlanEligibilityService::class)->getAllowedPlans($company);
        @endphp

        <div class="row g-4 mb-4">
            @forelse($allowedPlans as $plan)
                <div class="col-md-4">
                    <div class="plan-card {{ $loop->first ? 'featured' : '' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold mb-0 text-white">{{ $plan->name }}</h5>
                            @if($loop->first)
                                <span class="badge bg-primary text-white">Recommended</span>
                            @endif
                        </div>
                        <p class="text-sub small mb-3">{{ $plan->description }}</p>

                        <div class="mb-4">
                            <span class="fs-2 fw-bold text-white">₹{{ number_format($plan->monthly_price, 0) }}</span>
                            <span class="text-sub">/ month</span>
                        </div>

                        <ul class="list-unstyled text-sub small mb-4">
                            <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Up to {{ $plan->max_users == 0 ? 'Unlimited' : $plan->max_users }} Employees</li>
                            <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Up to {{ $plan->max_projects == 0 ? 'Unlimited' : $plan->max_projects }} Active Projects</li>
                            <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Up to {{ $plan->max_clients == 0 ? 'Unlimited' : $plan->max_clients }} Clients</li>
                            <li class="mb-2"><i class="bx bx-check text-success me-2"></i>{{ $plan->max_storage_mb >= 1024 ? ($plan->max_storage_mb / 1024) . ' GB' : $plan->max_storage_mb . ' MB' }} Storage</li>
                        </ul>

                        <form action="{{ Route::has('super-admin.subscriptions.store') ? route('super-admin.subscriptions.store') : (Route::has('super-admin.subscriptions.assign') ? route('super-admin.subscriptions.assign') : (Route::has('superadmin.subscriptions.store') ? route('superadmin.subscriptions.store') : (Route::has('subscriptions.store') ? route('subscriptions.store') : route('subscriptions.assign')))) }}" method="POST">
                            @csrf
                            <input type="hidden" name="company_id" value="{{ $company->id }}">
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <input type="hidden" name="billing_cycle" value="monthly">
                            <button type="submit" class="btn btn-upgrade">
                                <i class="bx bx-zap me-1"></i> Activate {{ $plan->name }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-sub py-4">
                    No eligible plans found. Please contact Super Admin.
                </div>
            @endforelse
        </div>

        <div class="contact-box d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="fw-bold mb-1"><i class="bx bx-headphone me-2 text-primary"></i>Need custom pricing or enterprise support?</h6>
                <p class="text-sub small mb-0">Our platform Super Admin team can assist you with manual activations or invoices.</p>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm"><i class="bx bx-log-out me-1"></i> Log Out</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
