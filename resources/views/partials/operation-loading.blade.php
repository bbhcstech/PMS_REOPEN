@once
<link rel="stylesheet" href="{{ asset('admin/assets/css/pms-operation-loading.css') }}?v={{ @filemtime(public_path('admin/assets/css/pms-operation-loading.css')) ?: 1 }}">
<script src="{{ asset('admin/assets/js/pms-operation-loading.js') }}?v={{ @filemtime(public_path('admin/assets/js/pms-operation-loading.js')) ?: 1 }}"></script>
@endonce
