<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') - {{ config('app.name', 'BBHPMS') }}</title>
    <script>
        (function () {
            document.documentElement.setAttribute('data-pms-theme', localStorage.getItem('pms-theme') || 'light');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="pms-tailwind-shell bg-slate-50 font-sans text-slate-900 antialiased">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-slate-950/50 lg:hidden"></div>

        <aside class="pms-super-sidebar fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-[#152047] text-white shadow-2xl transition-transform duration-200 lg:static lg:translate-x-0"
               :class="{ 'translate-x-0': sidebarOpen }">
            <div class="flex h-16 items-center gap-3 border-b border-white/10 px-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-400 text-[#152047]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-8 0h8m-10 4h12a2 2 0 002-2V7.5a2 2 0 00-.74-1.55l-6-4.9a2 2 0 00-2.52 0l-6 4.9A2 2 0 003 7.5V19a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-bold leading-tight">BBHPMS</p>
                    <p class="text-xs font-semibold text-cyan-200">Super Admin</p>
                </div>
            </div>

            <nav class="flex-1 space-y-3 overflow-y-auto px-4 py-7 text-base font-semibold">
                @php
                    $items = [
                        ['Dashboard', route('superadmin.dashboard'), request()->routeIs('superadmin.dashboard'), 'M3 12l9-9 9 9M4 10v10h5v-6h6v6h5V10'],
                        ['Companies', route('superadmin.dashboard') . '#companies', false, 'M3 21h18M5 21V5a2 2 0 012-2h7a2 2 0 012 2v16M9 7h1m-1 4h1m4-4h1m-1 4h1M9 21v-4h4v4'],
                        ['Subscriptions', route('superadmin.dashboard') . '#subscriptions', false, 'M15 5v14M5 7h14a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z'],
                        ['Plans & Pricing', route('superadmin.dashboard') . '#plans-pricing', false, 'M12 8c-2.21 0-4 1.12-4 2.5S9.79 13 12 13s4 1.12 4 2.5S14.21 18 12 18m0-10V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Modules', route('superadmin.dashboard') . '#modules', false, 'M4 6a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm9 0a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2V6zM4 15a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3zm9 0a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2v-3z'],
                        ['Company Admins', route('superadmin.admins.index'), request()->routeIs('superadmin.admins.*'), 'M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-4a4 4 0 100-8 4 4 0 000 8zm8 0a4 4 0 100-8 4 4 0 000 8z'],
                        ['Payments', route('superadmin.dashboard') . '#payments', false, 'M3 10h18M7 15h2m3 0h5M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z'],
                        ['Audit Logs', route('superadmin.dashboard') . '#audit-logs', false, 'M9 5h6m-7 4h8m-8 4h8m-8 4h5M6 3h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z'],
                    ];
                @endphp
                @foreach($items as [$label, $href, $active, $path])
                    <a href="{{ $href }}"
                       class="flex items-center gap-4 rounded-lg px-5 py-4 transition {{ $active ? 'bg-white/15 text-white shadow-lg shadow-cyan-950/20' : 'text-indigo-50 hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-6 w-6 text-cyan-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}" />
                        </svg>
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 p-4">
                @csrf
                <button class="flex w-full items-center gap-4 rounded-lg px-5 py-4 text-base font-semibold text-indigo-50 hover:bg-white/10">
                    <svg class="h-6 w-6 text-rose-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="pms-super-topbar sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="sidebarOpen = true" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-lg font-bold text-slate-900 sm:text-xl">@yield('title', 'Super Admin Dashboard')</h1>
                            <p class="hidden text-xs font-medium text-slate-500 sm:block">Control companies, admins, plans, modules, billing, and system settings.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="pms-tailwind-theme-toggle" aria-label="Toggle dark mode" title="Toggle theme">
                            <svg class="pms-tailwind-theme-moon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.8A8.5 8.5 0 1111.2 3a6.5 6.5 0 009.8 9.8z" />
                            </svg>
                            <svg class="pms-tailwind-theme-sun hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m14.95 6.95l-1.41-1.41M7.46 7.46 6.05 6.05m12.9 0-1.41 1.41M7.46 16.54l-1.41 1.41M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </button>
                        <div class="hidden rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 md:block">{{ auth()->user()->email }}</div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-500 text-sm font-bold text-white">SA</div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    <script>
        (function () {
            var root = document.documentElement;
            var buttons = document.querySelectorAll('.pms-tailwind-theme-toggle');

            function applyTheme(theme) {
                root.setAttribute('data-pms-theme', theme);
                localStorage.setItem('pms-theme', theme);
                buttons.forEach(function (button) {
                    var moon = button.querySelector('.pms-tailwind-theme-moon');
                    var sun = button.querySelector('.pms-tailwind-theme-sun');
                    button.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
                    button.setAttribute('title', theme === 'dark' ? 'Light mode' : 'Dark mode');
                    if (moon && sun) {
                        moon.classList.toggle('hidden', theme === 'dark');
                        sun.classList.toggle('hidden', theme !== 'dark');
                    }
                });
            }

            applyTheme(localStorage.getItem('pms-theme') || root.getAttribute('data-pms-theme') || 'light');
            buttons.forEach(function (button) {
                button.addEventListener('click', function () {
                    applyTheme(root.getAttribute('data-pms-theme') === 'dark' ? 'light' : 'dark');
                });
            });
        })();
    </script>
</body>
</html>
