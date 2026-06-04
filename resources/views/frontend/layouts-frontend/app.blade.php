<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName = 'Bitroxia PMS';
        $seoTitle = trim($__env->yieldContent('title', 'Bitroxia PMS - Project, HR and Team Management Software'));
        $seoDescription = trim($__env->yieldContent('meta_description', 'Bitroxia PMS helps teams plan projects, manage tasks, track attendance, handle leave, and report progress from one secure workspace.'));
        $seoKeywords = trim($__env->yieldContent('meta_keywords', 'project management software, PMS, task management, HR management, attendance tracking, leave management, team collaboration'));
        $canonicalUrl = url()->current();
        $logoUrl = asset('logo.png');
        $heroImageUrl = asset('frontend/img/bitroxia-pms-hero.png');
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="author" content="{{ $siteName }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" type="image/png" href="{{ $logoUrl }}">
    <link rel="apple-touch-icon" href="{{ $logoUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $heroImageUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $heroImageUrl }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Owl Carousel -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    <!-- Magnific Popup -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">

    <!-- Custom CSS -->
    <link href="{{ asset('frontend/css/bbh-pms.css') }}" rel="stylesheet">

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "Bitroxia PMS",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "description": @json($seoDescription),
            "url": @json(url('/')),
            "image": @json($heroImageUrl),
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
    <!-- Preloader -->
    <div id="preloader">
        <div class="loader">
            <div class="loader-square"></div>
            <div class="loader-square"></div>
            <div class="loader-square"></div>
            <div class="loader-square"></div>
            <div class="loader-square"></div>
            <div class="loader-square"></div>
            <div class="loader-square"></div>
        </div>
    </div>

    <!-- Navbar -->
    @include('frontend.layouts-frontend.header')

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    @include('frontend.layouts-frontend.footer')

    <!-- Back to Top -->
    <button id="backToTop" class="back-to-top-btn">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('frontend/js/bbh-pms.js') }}"></script>

    @stack('scripts')
</body>
</html>
