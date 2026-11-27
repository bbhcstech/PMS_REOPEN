@php
    $assistantRoute = null;
    if (\App\Services\TenantScope::isPlatformAdmin()) {
        $assistantRoute = 'super-admin.ai.index';
    } elseif (\Illuminate\Support\Facades\Auth::guard('web')->user()?->company_id) {
        $assistantRoute = 'company.ai.index';
    }
@endphp
@if($assistantRoute && \Illuminate\Support\Facades\Route::has($assistantRoute) && !request()->routeIs('company.ai.*', 'super-admin.ai.*', 'subscription.suspended'))
    @include('partials.ai-assistant-mark-styles')
    <a id="pms-ai-assistant-launcher" class="pms-ai-assistant-mark" href="{{ route($assistantRoute) }}" aria-label="Open your AI assistant" title="AI Assistant" data-live-preserve>
        <img src="{{ asset('logos/bitroxia_logo.png') }}" alt="" width="60" height="60">
    </a>
    <style>
        #pms-ai-assistant-launcher {
            --launcher-size: 60px;
            --launcher-offset: 24px;
            position: fixed;
            right: calc(var(--launcher-offset) + env(safe-area-inset-right, 0px));
            bottom: calc(var(--launcher-offset) + env(safe-area-inset-bottom, 0px));
            z-index: 1040;
            width: var(--launcher-size) !important;
            height: var(--launcher-size) !important;
            min-width: 0 !important;
            min-height: 0 !important;
            padding: 0 !important;
            color: #fff !important;
            text-decoration: none;
            box-shadow: 0 0 0 3px rgb(96 135 233 / .20), 0 8px 20px rgb(19 37 82 / .34);
            transition: opacity .18s ease, transform .18s ease, visibility .18s, border-color .18s ease, box-shadow .18s ease;
        }
        @media (hover: hover) {
            #pms-ai-assistant-launcher:hover {
                --ai-mark-border: #a1c3fa;
                transform: translateY(-2px);
                box-shadow: 0 0 0 4px rgb(96 135 233 / .24), 0 10px 24px rgb(19 37 82 / .40);
            }
        }
        #pms-ai-assistant-launcher:focus-visible {
            outline: 2px solid #648bff !important;
            outline-offset: 5px;
        }
        @media (max-width: 575px) {
            #pms-ai-assistant-launcher { --launcher-size: 54px; --launcher-offset: 16px; }
        }
        /* Never cover page controls: step aside while a menu is open or a control sits underneath. */
        body:has(.pms-table-export.is-open, .dropdown-menu.show, .select2-container--open, .swal2-container) #pms-ai-assistant-launcher,
        #pms-ai-assistant-launcher.is-tucked { opacity: 0; visibility: hidden; transform: translateY(10px); pointer-events: none; }
        @media (prefers-reduced-motion: reduce) {
            #pms-ai-assistant-launcher { transition: none; }
            #pms-ai-assistant-launcher:is(:hover, .is-tucked) { transform: none; }
        }
    </style>
    <script>
        (function () {
            var launcher = document.getElementById('pms-ai-assistant-launcher');
            if (!launcher || !document.elementsFromPoint) return;
            var interactive = 'a[href], button, input, select, textarea, [role="button"], [role="menuitem"], .pms-table-export, .dropdown-menu, .pagination';
            var pending = false;
            function covered() {
                var wasTucked = launcher.classList.contains('is-tucked');
                var rect = launcher.getBoundingClientRect();
                if (!rect.width || !rect.height) return false;
                var yOffset = wasTucked ? -10 : 0;
                var x = rect.left + rect.width / 2;
                var y = rect.top + yOffset + rect.height / 2;
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
                var next = covered();
                if (launcher.classList.contains('is-tucked') !== next) {
                    launcher.classList.toggle('is-tucked', next);
                }
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
