<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="generator" content="Rotor {{ ROTOR_VERSION }}">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') :: {{ setting('title') }}</title>
    <link rel="canonical" href="@yield('canonical', request()->url())">
    <link rel="icon" href="/favicon.ico">
    <link href="{{ route('news.rss') }}" title="RSS News" type="application/rss+xml" rel="alternate">
    @vite('resources/themes/waphack/sass/app.scss')
    @stack('styles')
    @hook('head')
</head>
<body>

@include('themes/waphack/navbar')

<div id="content">
    <div class="app-title">
        @yield('header')
        @hook('header')
    </div>
    @yield('flash')
    @yield('advertTop')
    @yield('advertAdmin')
    @yield('advertUser')
    @hook('contentStart')
    @yield('content')
    @hook('contentEnd')
    @yield('advertBottom')
</div>

@include('themes/waphack/footer')
@vite('resources/themes/waphack/js/app.js')
@stack('scripts')
@hook('footer')
</body>
</html>
