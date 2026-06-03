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
    <body class="pms-tailwind-shell font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
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
