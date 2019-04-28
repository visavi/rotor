<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <title>@yield('title') - {{ setting('title') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="keywords" content="@yield('keywords', setting('keywords'))">
</head>
<body>
    @yield('content')
</body>
</html>
