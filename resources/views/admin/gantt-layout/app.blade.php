@include('admin.layout.header')

<main>
    @include('partials.page-back-navigation')
    @yield('content')
</main>

@include('admin.layout.footer')
