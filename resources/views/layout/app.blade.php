<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>

    <meta charset="utf-8" />
    <title> @yield('title') || Stack Master </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />

    {{-- App favicon --}}
    <link rel="shortcut icon" href="{{ asset('admin/assets/images/default/favicon.png') }}">

    {{-- all css links --}}
    @include('assets.styles')

    <!-- Layout config Js -->
    <script src="{{ asset('admin/assets/js/layout.js') }}"></script>
</head>

<body>
    <div id="layout-wrapper">

        {{-- Include Header --}}
        @include('navigations.header')


        {{-- Include Sidebar --}}
        @include('navigations.sidebar')

        {{-- Vertical Overlay --}}
        <div class="vertical-overlay"></div>

        {{-- All page content will be populated here --}}
        <div class="main-content">

            @yield('content')

            {{-- Footer include --}}
            @include('navigations.footer')
        </div>

    </div>


    {{-- start back-to-top --}}
    @include('partials.back-to-top')

    {{-- preloader --}}
    @include('partials.loader')

    {{-- seetings icon for theme changing --}}
    @include('partials.setting')

    {{-- Theme Settings --}}
    @include('partials.themes')

    {{-- JAVASCRIPT --}}
    @include('assets.scripts')
</body>

</html>
