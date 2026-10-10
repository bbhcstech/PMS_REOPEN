@php
    $assistantRoute = null;
    if (\App\Services\TenantScope::isPlatformAdmin()) {
        $assistantRoute = 'super-admin.ai.index';
    } elseif (\Illuminate\Support\Facades\Auth::guard('web')->user()?->company_id) {
        $assistantRoute = 'company.ai.index';
    }
@endphp
@if($assistantRoute && \Illuminate\Support\Facades\Route::has($assistantRoute) && !request()->routeIs('company.ai.*', 'super-admin.ai.*', 'subscription.suspended'))
    <a id="pms-ai-assistant-launcher" href="{{ route($assistantRoute) }}" aria-label="Open your AI assistant" data-live-preserve>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="4" y="7" width="16" height="13" rx="4" />
            <path d="M12 7V3M9 16h6M1 11v5m22-5v5" />
            <circle cx="8" cy="12" r="1" /><circle cx="16" cy="12" r="1" />
        </svg>
        <span>AI Assistant</span>
    </a>
    <style>
        #pms-ai-assistant-launcher { position: fixed; right: 24px; bottom: 90px; z-index: 1040; display: inline-flex; align-items: center; gap: 10px; padding: 12px 18px; border: 1px solid #93c5fd; border-radius: 28px; background: #2563eb !important; color: #fff !important; text-decoration: none; box-shadow: 0 6px 20px rgba(37, 99, 235, .3); font-weight: 600; }
        #pms-ai-assistant-launcher span, #pms-ai-assistant-launcher svg { color: #fff !important; -webkit-text-fill-color: #fff !important; }
        #pms-ai-assistant-launcher svg { background: transparent !important; flex-shrink: 0; }
        #pms-ai-assistant-launcher:hover { background: #1d4ed8 !important; }
        #pms-ai-assistant-launcher:focus-visible { outline: 3px solid #93c5fd; outline-offset: 4px; }
        @media (max-width: 575px) { #pms-ai-assistant-launcher { right: 16px; bottom: 90px; padding: 10px 14px; } }
    </style>
@endif
