@include('admin.layout.header')
@include('admin.layout.manu')

@yield('content')

@include('partials.password-changed-modal')
@include('admin.layout.toasts')
@include('admin.layout.footer')

