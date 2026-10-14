@include('admin.layout.header')
@include('admin.layout.manu')

@include('partials.page-back-navigation')
<div data-live-records data-live-identity="@include('partials.live-records-identity')" style="display:contents">
@yield('content')
</div>

@include('partials.password-changed-modal')
@include('admin.layout.toasts')
@include('admin.layout.footer')
