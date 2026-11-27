<!doctype html>
<html lang="en" data-pms-theme="light">
<head>
    @include('partials.pwa')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Onboarding{{ !empty($company?->name) ? ' — ' . $company->name : '' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script>
        (function () {
            var theme = localStorage.getItem('pms-theme') || localStorage.getItem('bitroxia-theme') || 'light';
            document.documentElement.setAttribute('data-pms-theme', theme);
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <style>
        :root {
            --ob-bg: #f4f7fc;
            --ob-surface: #ffffff;
            --ob-subtle: #f8fafc;
            --ob-border: #dce5f1;
            --ob-ink: #0f172a;
            --ob-muted: #52637b;
            --ob-primary: #2563eb;
            --ob-primary-hover: #1d4ed8;
            --ob-success: #059669;
            --ob-success-bg: #ecfdf5;
            --ob-warn: #d97706;
            --ob-warn-bg: #fffbeb;
            --ob-danger: #dc2626;
            --ob-danger-bg: #fef2f2;
        }
        html[data-pms-theme="dark"] {
            color-scheme: dark;
            --ob-bg: #070b1a;
            --ob-surface: #0f1530;
            --ob-subtle: #141b3d;
            --ob-border: rgba(238, 241, 251, 0.12);
            --ob-ink: #eef1fb;
            --ob-muted: #9aa3c7;
            --ob-primary: #3b82f6;
            --ob-primary-hover: #60a5fa;
            --ob-success: #34d399;
            --ob-success-bg: rgba(16, 185, 129, 0.14);
            --ob-warn: #fbbf24;
            --ob-warn-bg: rgba(245, 158, 11, 0.14);
            --ob-danger: #f87171;
            --ob-danger-bg: rgba(239, 68, 68, 0.14);
        }
        * { box-sizing: border-box; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--ob-bg);
            color: var(--ob-ink);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .status-card {
            width: min(560px, 100%);
            background: var(--ob-surface);
            border: 1px solid var(--ob-border);
            border-radius: 22px;
            padding: 36px 32px;
            box-shadow: 0 22px 54px rgba(15, 23, 42, 0.12);
            text-align: center;
        }
        .brand-row {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid var(--ob-border);
            background: var(--ob-subtle);
            font-weight: 700;
            font-size: 0.9rem;
        }
        .brand-row img { max-height: 26px; width: auto; border-radius: 6px; }
        .state-badge {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 18px;
        }
        .state-success { background: var(--ob-success-bg); color: var(--ob-success); }
        .state-warn { background: var(--ob-warn-bg); color: var(--ob-warn); }
        .state-danger { background: var(--ob-danger-bg); color: var(--ob-danger); }
        h1 { font-size: 1.45rem; font-weight: 800; margin: 0 0 10px; letter-spacing: -0.02em; }
        p { margin: 0 0 18px; color: var(--ob-muted); line-height: 1.65; font-size: 0.96rem; }
        .meta-box {
            text-align: left;
            padding: 16px 18px;
            border-radius: 14px;
            border: 1px solid var(--ob-border);
            background: var(--ob-subtle);
            margin-bottom: 22px;
            font-size: 0.88rem;
        }
        .meta-row { display: flex; justify-content: space-between; gap: 12px; padding: 5px 0; }
        .meta-row strong { color: var(--ob-ink); }
        .meta-row span { color: var(--ob-muted); }
        .contact-line { font-size: 0.84rem; color: var(--ob-muted); margin-top: 12px; }
        .action-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            border-radius: 12px;
            background: var(--ob-primary);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.92rem;
        }
        .action-link:hover { background: var(--ob-primary-hover); }
    </style>
</head>
<body>
    @php
        $companyName = $company?->name ?: config('app.name');
        $logoUrl = ($company && method_exists($company, 'logoUrl')) ? $company->logoUrl() : null;
        $isSuccess = in_array($state, ['submitted_success', 'submitted', 'approved'], true);
        $isWarn = in_array($state, ['expired'], true);
        $heading = match ($state) {
            'submitted_success' => 'Application Submitted Successfully',
            'submitted' => 'Application Already Submitted',
            'approved' => 'Onboarding Application Approved',
            'expired' => 'Invitation Link Expired',
            'revoked' => 'Invitation Revoked',
            'rejected' => 'Application Closed',
            default => 'Invitation Link Not Valid',
        };
        $icon = $isSuccess ? 'fa-circle-check' : ($isWarn ? 'fa-hourglass-end' : 'fa-circle-exclamation');
        $badgeClass = $isSuccess ? 'state-success' : ($isWarn ? 'state-warn' : 'state-danger');
    @endphp
    <main class="status-card">
        <div class="brand-row">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="{{ $companyName }}">
            @else
                <i class="fas fa-building text-primary"></i>
            @endif
            <span>{{ $companyName }}</span>
        </div>

        <div>
            <span class="state-badge {{ $badgeClass }}"><i class="fas {{ $icon }}"></i></span>
        </div>

        <h1>{{ $heading }}</h1>
        <p>{{ $message }}</p>

        @if(!empty($application))
            <div class="meta-box">
                <div class="meta-row"><span>Applicant</span><strong>{{ $application->name }}</strong></div>
                <div class="meta-row"><span>Email</span><strong>{{ $application->email }}</strong></div>
                <div class="meta-row"><span>Status</span><strong>{{ $application->status }}</strong></div>
                @if($application->submitted_at)
                    <div class="meta-row"><span>Submitted</span><strong>{{ $application->submitted_at->format('d M Y, h:i A') }}</strong></div>
                @endif
            </div>
        @endif

        @if($state === 'approved')
            <a href="{{ route('login') }}" class="action-link"><i class="fas fa-right-to-bracket"></i> Go to Sign In</a>
        @endif

        @if(!empty($company?->email) || !empty($company?->phone))
            <div class="contact-line">
                Need assistance? Contact {{ $companyName }} HR
                @if(!empty($company?->email)) · {{ $company->email }} @endif
                @if(!empty($company?->phone)) · {{ $company->phone }} @endif
            </div>
        @endif
    </main>
</body>
</html>
