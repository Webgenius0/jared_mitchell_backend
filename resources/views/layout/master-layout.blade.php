<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Premium Multipurpose Admin & Dashboard Template">
    <meta name="author" content="Themesbrand">

    {{-- CSRF token — consumed by Axios globally --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- App favicon --}}
    <link rel="shortcut icon" href="{{ asset('admin/assets/images/default/favicon.png') }}">

    {{-- All CSS (Bootstrap, Icons, App, custom, NProgress, Toastify) --}}
    @include('assets.styles')

    {{-- Layout config --}}
    <script src="{{ asset('admin/assets/js/layout.js') }}"></script>

    {{-- Page-level styles --}}
    @stack('styles')
</head>

<body>

    <div id="layout-wrapper">

        {{-- Header --}}
        @include('navigations.header')

        {{-- Sidebar --}}
        @include('navigations.sidebar')

        {{-- Vertical overlay --}}
        <div class="vertical-overlay"></div>

        {{-- Page content --}}
        <div class="main-content">

            @yield('content')

            {{-- Footer --}}
            @include('navigations.footer')

        </div>

    </div>

    {{-- Back to top --}}
    @include('partials.back-to-top')

    {{-- Preloader --}}
    @include('partials.loader')

    {{-- Theme settings --}}
    @include('partials.setting')
    @include('partials.themes')

    {{-- Core scripts (Bootstrap, Axios, NProgress, Toastify, etc.) --}}
    @include('assets.scripts')

    {{-- Global app bootstrap: NProgress + Toast + Axios interceptors --}}
    @include('partials.app-init')

    {{-- Page-level scripts --}}
    @stack('scripts')

</body>

</html>
