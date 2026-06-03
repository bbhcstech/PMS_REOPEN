<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <script>
            (function () {
                document.documentElement.setAttribute('data-pms-theme', localStorage.getItem('pms-theme') || 'light');
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="pms-tailwind-shell font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div class="fixed right-4 top-4">
                <button type="button" class="pms-tailwind-theme-toggle" aria-label="Toggle dark mode" title="Toggle theme">
                    <svg class="pms-tailwind-theme-moon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.8A8.5 8.5 0 1111.2 3a6.5 6.5 0 009.8 9.8z" />
                    </svg>
                    <svg class="pms-tailwind-theme-sun hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m14.95 6.95l-1.41-1.41M7.46 7.46 6.05 6.05m12.9 0-1.41 1.41M7.46 16.54l-1.41 1.41M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
            </div>
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="pms-guest-card w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
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
