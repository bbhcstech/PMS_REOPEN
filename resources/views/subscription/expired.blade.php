<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired</title>
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; background:#0f172a; color:#f8fafc; font-family:system-ui,sans-serif; }
        main { width:min(520px,calc(100% - 64px)); padding:32px; background:#1e293b; border:1px solid #334155; border-radius:20px; }
        h1 { font-size:28px; } p { color:#cbd5e1; line-height:1.6; } strong { color:#f8fafc; }
        button { padding:12px 20px; border:0; border-radius:8px; background:#2563eb; color:white; cursor:pointer; font:inherit; }
    </style>
</head>
<body data-restriction-status="expired" data-restriction-url="{{ route('subscription.suspended') }}">
<main>
    <h1>Subscription Expired</h1>
    <p><strong>{{ $company->name }}</strong> can no longer access workspace features.</p>
    <p>Assigned plan: <strong>{{ $subscription?->plan?->name ?? 'No assigned plan' }}</strong><br>
       Expired on: <strong>{{ $subscription?->ends_at?->format('d M Y') ?? $company->trial_ends_at?->format('d M Y') ?? 'N/A' }}</strong></p>
    <p>Your assigned plan remains unchanged. Contact Super Admin to extend it. Only Super Admin can renew your subscription and restore access.</p>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Log out</button></form>
</main>
<script src="{{ asset('admin/assets/js/pms-restriction-status.js') }}?v={{ @filemtime(public_path('admin/assets/js/pms-restriction-status.js')) }}" defer></script>
</body>
</html>
