<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#1268ff">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Bitroxia PMS">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('pwa/icons/icon-180x180.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('pwa/icons/icon-192x192.png') }}">
<link rel="stylesheet" href="{{ asset('pwa.css') }}?v={{ @filemtime(public_path('pwa.css')) ?: 1 }}">
<script src="{{ asset('pwa-register.js') }}?v={{ @filemtime(public_path('pwa-register.js')) ?: 1 }}" defer></script>
