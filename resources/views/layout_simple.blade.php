<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <title>@yield('title') - {{ setting('title') }}</title>
    <meta charset="utf-8">
    <meta name="description" content="@yield('description', setting('description'))">
    @include('app/_styles')
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
    @include('app/_scripts')
</body>
</html>
