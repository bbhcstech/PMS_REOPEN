@php
    $assistantRoute = null;
    if (\App\Services\TenantScope::isPlatformAdmin()) {
        $assistantRoute = 'super-admin.ai.index';
    } elseif (\Illuminate\Support\Facades\Auth::guard('web')->user()?->company_id) {
        $assistantRoute = 'company.ai.index';
    }
@endphp
@if($assistantRoute && \Illuminate\Support\Facades\Route::has($assistantRoute) && !request()->routeIs('company.ai.*', 'super-admin.ai.*', 'subscription.suspended'))
    <a id="pms-ai-assistant-launcher" href="{{ route($assistantRoute) }}" aria-label="Open your AI assistant" title="AI Assistant" data-live-preserve>
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true">
            <path d="M10.5 2.5l2.2 5.8 5.8 2.2-5.8 2.2-2.2 5.8-2.2-5.8-5.8-2.2 5.8-2.2 2.2-5.8zM19 14l1 2.8 2.8 1-2.8 1-1 2.8-1-2.8-2.8-1 2.8-1 1-2.8z" />
        </svg>
    </a>
    <style>
        #pms-ai-assistant-launcher {
            --launcher-size: 58px;
            position: fixed;
            right: max(24px, env(safe-area-inset-right));
            bottom: max(24px, env(safe-area-inset-bottom));
            z-index: 1040;
            display: grid;
            place-items: center;
            box-sizing: border-box;
            width: var(--launcher-size) !important;
            height: var(--launcher-size) !important;
            min-width: 0 !important;
            min-height: 0 !important;
            padding: 0 !important;
            border: 1px solid rgba(255, 255, 255, .38);
            border-radius: 50% !important;
            background: linear-gradient(145deg, #2857d1, #6437c7) !important;
            color: #fff !important;
            text-decoration: none;
            box-shadow: 0 8px 22px rgba(40, 70, 168, .34), inset 0 1px 0 rgba(255, 255, 255, .18);
            transition: opacity .18s ease, transform .18s ease, visibility .18s;
        }
        #pms-ai-assistant-launcher svg { background: transparent !important; color: #fff !important; stroke: #fff !important; }
        #pms-ai-assistant-launcher:hover { background: linear-gradient(145deg, #214abd, #512aac) !important; transform: translateY(-2px); }
        #pms-ai-assistant-launcher:focus-visible { outline: 3px solid #5e8bff; outline-offset: 4px; }
        @media (max-width: 575px) {
            #pms-ai-assistant-launcher { --launcher-size: 54px; right: max(16px, env(safe-area-inset-right)); bottom: max(16px, env(safe-area-inset-bottom)); }
        }
        @media (prefers-reduced-motion: reduce) { #pms-ai-assistant-launcher { transition: none; } }
        /* Never cover page controls: step aside while a menu is open or a control sits underneath. */
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
                var x = rect.left + rect.width / 2;
                var y = rect.top + rect.height / 2;
                var radius = rect.width / 2 - 4;
                var points = [[x, y], [x - radius, y], [x + radius, y], [x, y - radius], [x, y + radius]];
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
