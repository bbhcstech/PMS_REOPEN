@extends('admin.layout.app')

@section('title', ($section ?? 'Finance') . ' - Coming Soon')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y pms-coming-soon-page">
    <div class="pms-coming-soon-card" role="status">
        <div class="pms-coming-soon-icon" aria-hidden="true">
            <i class="bx bx-wallet"></i>
        </div>
        <span class="pms-coming-soon-badge">Coming Soon</span>
        <h1>{{ $section ?? 'Finance' }}</h1>
        <p>{{ $message ?? 'This finance section is coming soon. It is not developed yet.' }}</p>
        <p class="pms-coming-soon-note">Finance features are being built and will be available in an upcoming release. No finance data can be viewed or changed until then.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary pms-coming-soon-btn">
            <i class="bx bx-home-alt me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<style>
    .pms-coming-soon-page { display: flex; align-items: center; justify-content: center; min-height: 65vh; }
    .pms-coming-soon-card {
        width: min(560px, 100%);
        text-align: center;
        padding: 44px 32px;
        border-radius: 22px;
        background: #ffffff;
        border: 1px solid rgba(47, 107, 255, 0.14);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    }
    .pms-coming-soon-icon {
        width: 76px; height: 76px; margin: 0 auto 18px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 22px; font-size: 38px;
        background: linear-gradient(135deg, #EEF2FF, #DBEAFE);
        color: #2F6BFF;
    }
    .pms-coming-soon-icon i { color: #2F6BFF !important; -webkit-text-fill-color: #2F6BFF !important; }
    .pms-coming-soon-badge {
        display: inline-block; margin-bottom: 12px; padding: 5px 14px;
        border-radius: 999px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase;
        background: #FEF3C7; color: #92400E !important; -webkit-text-fill-color: #92400E !important;
    }
    .pms-coming-soon-card h1 { font-size: 1.7rem; font-weight: 800; margin: 0 0 10px; color: #0F172A !important; -webkit-text-fill-color: #0F172A !important; }
    .pms-coming-soon-card p { margin: 0 0 10px; font-size: 1rem; color: #334155 !important; -webkit-text-fill-color: #334155 !important; }
    .pms-coming-soon-card .pms-coming-soon-note { font-size: 0.88rem; color: #64748B !important; -webkit-text-fill-color: #64748B !important; margin-bottom: 24px; }
    .pms-coming-soon-btn { border-radius: 12px; padding: 10px 22px; font-weight: 700; }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .pms-coming-soon-card,
    html body.dark-mode .pms-coming-soon-card {
        background: #0F1530 !important;
        border-color: rgba(96, 165, 250, 0.22) !important;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.45) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .pms-coming-soon-icon,
    html body.dark-mode .pms-coming-soon-icon { background: rgba(47, 107, 255, 0.18) !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .pms-coming-soon-icon i { color: #93C5FD !important; -webkit-text-fill-color: #93C5FD !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .pms-coming-soon-card h1 { color: #EEF1FB !important; -webkit-text-fill-color: #EEF1FB !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .pms-coming-soon-card p { color: #C7CFEA !important; -webkit-text-fill-color: #C7CFEA !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .pms-coming-soon-card .pms-coming-soon-note { color: #9AA3C7 !important; -webkit-text-fill-color: #9AA3C7 !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .pms-coming-soon-badge { background: rgba(245, 158, 11, 0.18) !important; color: #FCD34D !important; -webkit-text-fill-color: #FCD34D !important; }
</style>
@endsection
