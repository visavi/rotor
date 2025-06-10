<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ setting('title') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ mix('/assets/dist/css/default.css') }}">
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        @include('app/_flash')
        @yield('content')
    </div>

    <script src="{{ mix('/assets/dist/js/manifest.js') }}"></script>
    <script src="{{ mix('/assets/dist/js/vendor.js') }}"></script>
    <script src="{{ mix('/assets/dist/js/lang.js') }}"></script>
    <script src="{{ mix('/assets/dist/js/default.js') }}"></script>
    @stack('scripts')
</body>
</html>
