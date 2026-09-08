<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (function() {
            var saved = localStorage.getItem('bitroxia-theme');
            if (saved === 'light' || saved === 'dark') {
                document.documentElement.setAttribute('data-theme', saved);
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    @php
        $siteName = 'Bitroxia PMS';
        $seoTitle = trim($__env->yieldContent('title', 'Bitroxia PMS — Project, HR & Team Management, Unified'));
        $seoDescription = trim($__env->yieldContent('meta_description', 'Bitroxia PMS brings projects, tasks, attendance, leave, tickets, clients and reporting into one connected workspace built for fast-moving teams.'));
        $seoKeywords = trim($__env->yieldContent('meta_keywords', 'project management software, PMS, task management, HR management, attendance tracking, leave management, team collaboration, business software'));
        $canonicalUrl = url()->current();
        $logoUrl = asset('logo.png');
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="author" content="{{ $siteName }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="theme-color" content="#2F6BFF">
    <meta name="application-name" content="{{ $siteName }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" type="image/png" href="{{ $logoUrl }}">
    <link rel="apple-touch-icon" href="{{ $logoUrl }}">
    @include('partials.pwa')

    <!-- Open Graph / Facebook -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $logoUrl }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $logoUrl }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('frontend/css/base.css') }}?v={{ file_exists(public_path('frontend/css/base.css')) ? filemtime(public_path('frontend/css/base.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/nav-hero.css') }}?v={{ file_exists(public_path('frontend/css/nav-hero.css')) ? filemtime(public_path('frontend/css/nav-hero.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/sections.css') }}?v={{ file_exists(public_path('frontend/css/sections.css')) ? filemtime(public_path('frontend/css/sections.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/footer-misc.css') }}?v={{ file_exists(public_path('frontend/css/footer-misc.css')) ? filemtime(public_path('frontend/css/footer-misc.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/pages.css') }}?v={{ file_exists(public_path('frontend/css/pages.css')) ? filemtime(public_path('frontend/css/pages.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/responsive.css') }}?v={{ file_exists(public_path('frontend/css/responsive.css')) ? filemtime(public_path('frontend/css/responsive.css')) : time() }}">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Bitroxia PMS",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": @json($seoDescription),
        "url": @json(url('/')),
        "image": @json($logoUrl),
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "featureList": [
            "Project management",
            "Task management",
            "Attendance tracking",
            "Leave management",
            "Ticket management",
            "Client management",
            "Reports and analytics"
        ]
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Bitroxia PMS",
        "url": @json(url('/')),
        "logo": @json($logoUrl),
        "email": "info@bitroxia.com",
        "areaServed": "Worldwide"
    }
    </script>

    @stack('styles')
</head>
<body>
    <a href="#main" class="skip-link">Skip to content</a>

    <!-- Navbar -->
    @include('frontend.layouts-frontend.header')

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    @include('frontend.layouts-frontend.footer')

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
    </button>

    @include('partials.pwa-install')

    <!-- Scripts -->
    <script src="{{ asset('frontend/js/leads.js') }}?v={{ file_exists(public_path('frontend/js/leads.js')) ? filemtime(public_path('frontend/js/leads.js')) : time() }}"></script>
    <script src="{{ asset('frontend/js/main.js') }}?v={{ file_exists(public_path('frontend/js/main.js')) ? filemtime(public_path('frontend/js/main.js')) : time() }}" defer></script>

    @stack('scripts')
</body>
</html>
