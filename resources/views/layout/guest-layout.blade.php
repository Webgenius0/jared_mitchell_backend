<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>

    <meta charset="utf-8" />
     <title> @yield('title') || {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Layout config Js -->
    <script src="assets/js/layout.js"></script>

    {{-- all css links --}}
    @include('assets.styles')
</head>

<body>

    @yield('content')


    <!-- JAVASCRIPT -->
        @include('assets.scripts')

    <!-- particles js -->
    <script src=" {{ asset('admin/assets/libs/particles.js/particles.js') }} "></script>
    <!-- particles app js -->
    <script src=" {{ asset('admin/assets/js/pages/particles.app.js') }} "></script>
    <!-- password-addon init -->
    <script src=" {{ asset('admin/assets/js/pages/password-addon.init.js') }} "></script>
</body>

</html>
