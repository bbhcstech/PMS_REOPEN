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
        <img src="{{ asset('logos/bitroxia_logo.png') }}" alt="" width="58" height="58">
    </a>
    <style>
        #pms-ai-assistant-launcher { position: fixed; right: 24px; bottom: 24px; z-index: 1040; display: inline-flex; align-items: center; gap: 9px; padding: 11px 18px; border: 1px solid rgba(147, 197, 253, 0.45); border-radius: 999px; background: #2563eb !important; color: #fff !important; text-decoration: none; box-shadow: 0 8px 24px rgba(37, 99, 235, 0.32); font-size: 0.86rem; font-weight: 600; }
        #pms-ai-assistant-launcher span, #pms-ai-assistant-launcher svg { color: #fff !important; -webkit-text-fill-color: #fff !important; }
        #pms-ai-assistant-launcher svg { background: transparent !important; flex-shrink: 0; width: 20px; height: 20px; }
        #pms-ai-assistant-launcher:hover { background: #1d4ed8 !important; transform: translateY(-1px); box-shadow: 0 10px 28px rgba(37, 99, 235, 0.4); }
        #pms-ai-assistant-launcher:focus-visible { outline: 3px solid #93c5fd; outline-offset: 4px; }
        @media (max-width: 575px) { #pms-ai-assistant-launcher { right: 16px; bottom: 20px; padding: 10px 14px; } }
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
