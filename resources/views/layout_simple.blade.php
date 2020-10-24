<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <meta charset="utf-8">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ setting('title') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ mix('/themes/default/dist/app.css') }}">
</head>
<body>
    <div class="container">
        @yield('content')
    </div>

    <script src="{{ mix('/assets/js/dist/manifest.js') }}"></script>
    <script src="{{ mix('/assets/js/dist/vendor.js') }}"></script>
    <script src="{{ mix('/assets/js/dist/lang.js') }}"></script>
    <script src="{{ mix('/themes/default/dist/app.js') }}"></script>
</body>
</html>
