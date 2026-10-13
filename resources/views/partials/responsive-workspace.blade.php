@php
    $mobileStyleVersion = @filemtime(public_path('admin/assets/css/pms-mobile.css')) ?: 1;
    $mobileScriptVersion = @filemtime(public_path('admin/assets/js/pms-mobile.js')) ?: 1;
@endphp
<script>document.documentElement.setAttribute('data-pms-shell', @json($responsiveShell ?? 'admin'));</script>
<link rel="stylesheet" href="{{ asset('admin/assets/css/pms-mobile.css') }}?v={{ $mobileStyleVersion }}">
<script src="{{ asset('admin/assets/js/pms-mobile.js') }}?v={{ $mobileScriptVersion }}" defer></script>
