<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="{{ request()->cookie('theme') ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ setting('title') }}</title>
    @vite('public/assets/themes/default/sass/app.scss')
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        @include('app/_flash')
        @yield('content')
    </div>

    @vite('public/assets/themes/default/js/app.js')
    @stack('scripts')
</body>
</html>
