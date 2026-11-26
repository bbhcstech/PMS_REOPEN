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
        #pms-ai-assistant-launcher { position: fixed; right: 24px; bottom: 24px; z-index: 1040; display: inline-flex; align-items: center; gap: 9px; padding: 11px 18px; border: 1px solid rgba(255, 255, 255, 0.22); border-radius: 999px; background: linear-gradient(135deg, #2F6BFF 0%, #8B5CF6 100%) !important; color: #fff !important; text-decoration: none; box-shadow: 0 10px 26px rgba(47, 107, 255, 0.34); font-size: 0.86rem; font-weight: 600; letter-spacing: .01em; }
        #pms-ai-assistant-launcher span, #pms-ai-assistant-launcher svg { color: #fff !important; -webkit-text-fill-color: #fff !important; }
        #pms-ai-assistant-launcher svg { background: transparent !important; flex-shrink: 0; width: 20px; height: 20px; }
        #pms-ai-assistant-launcher:hover { background: linear-gradient(135deg, #1E4FCC 0%, #7C3AED 100%) !important; transform: translateY(-1px); box-shadow: 0 12px 30px rgba(47, 107, 255, 0.42); }
        #pms-ai-assistant-launcher:focus-visible { outline: 3px solid rgba(47, 107, 255, 0.45); outline-offset: 4px; }
        @media (max-width: 575px) { #pms-ai-assistant-launcher { right: 16px; bottom: 20px; padding: 10px 14px; } }
        /* Never cover page controls: step aside while a menu is open or a control sits underneath. */
        #pms-ai-assistant-launcher { transition: opacity .18s ease, transform .18s ease, visibility .18s; }
        body:has(.pms-table-export.is-open, .dropdown-menu.show, .select2-container--open, .swal2-container) #pms-ai-assistant-launcher,
        #pms-ai-assistant-launcher.is-tucked { opacity: 0; visibility: hidden; transform: translateY(10px); pointer-events: none; }
    </style>
    <script>
        (function () {
            var launcher = document.getElementById('pms-ai-assistant-launcher');
            if (!launcher || !document.elementsFromPoint) return;
            var interactive = 'a[href], button, input, select, textarea, [role="button"], [role="menuitem"], .pms-table-export, .dropdown-menu, .pagination';
            var pending = false;
            function covered() {
                var wasTucked = launcher.classList.contains('is-tucked');
                launcher.classList.remove('is-tucked');
                var rect = launcher.getBoundingClientRect();
                if (wasTucked) launcher.classList.add('is-tucked');
                if (!rect.width || !rect.height) return false;
                var points = [[rect.left + 6, rect.top + 6], [rect.right - 6, rect.top + 6], [rect.left + 6, rect.bottom - 6], [rect.right - 6, rect.bottom - 6], [rect.left + rect.width / 2, rect.top + rect.height / 2]];
                for (var i = 0; i < points.length; i++) {
                    var stack = document.elementsFromPoint(points[i][0], points[i][1]);
                    for (var j = 0; j < stack.length; j++) {
                        var el = stack[j];
                        if (el === launcher || launcher.contains(el)) continue;
                        if (el.closest && el.closest(interactive)) return true;
                    }
                }
                return false;
            }
            function update() {
                pending = false;
                launcher.classList.toggle('is-tucked', covered());
            }
            function schedule() {
                if (pending) return;
                pending = true;
                requestAnimationFrame(update);
            }
            ['scroll', 'resize', 'click', 'keyup'].forEach(function (type) { window.addEventListener(type, schedule, { passive: true, capture: true }); });
            document.addEventListener('pms:records-updated', schedule);
            setInterval(schedule, 1500);
            schedule();
        })();
    </script>
@endif
