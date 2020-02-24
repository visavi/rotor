<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <title>@yield('title') - {{ setting('title') }}</title>
    <meta charset="utf-8">
    <meta name="description" content="@yield('description', setting('description'))">
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>
