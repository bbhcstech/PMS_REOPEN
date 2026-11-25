<script>
    window.PMSClockConfig = { timeZone: @json(config('app.timezone', 'Asia/Kolkata')), serverNow: {{ now()->getTimestampMs() }} };
</script>
<script src="{{ asset('admin/assets/js/pms-time.js') }}?v={{ filemtime(public_path('admin/assets/js/pms-time.js')) }}"></script>
