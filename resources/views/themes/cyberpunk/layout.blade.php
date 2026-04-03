<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#00f0ff">
    <meta name="generator" content="Rotor {{ ROTOR_VERSION }}">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="image" content="{{ asset('/assets/img/images/icon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ setting('title') }}</title>
    <link rel="canonical" href="@yield('canonical', request()->url())">
    <link rel="icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/assets/img/images/icon.svg">
    <link rel="icon" type="image/png" href="/assets/img/images/icon.png" sizes="128x128">
    <link rel="apple-touch-icon" href="/assets/img/images/apple-touch-icon.png">
    <link href="{{ route('news.rss') }}" title="RSS News" type="application/rss+xml" rel="alternate">
    @vite('resources/themes/cyberpunk/sass/app.scss')
    @stack('styles')
    @hook('head')
</head>
<body class="app">

@include('themes/cyberpunk/navbar')

<div class="app-body">
<main class="app-content">
    <div class="app-title">
        @yield('header')
        @yield('breadcrumb')
        @hook('header')
    </div>

    @yield('flash')

    <div class="mb-2">
        @yield('advertTop')
        @yield('advertAdmin')
        @yield('advertUser')
    </div>

    @hook('contentStart')
    @yield('content')
    @hook('contentEnd')
    @yield('advertBottom')
</main>
@include('themes/cyberpunk/sidebar')
</div>

@include('themes/cyberpunk/footer')
@vite('resources/themes/cyberpunk/js/app.js')
@stack('scripts')
@hook('footer')
<div class="scrollup"></div>
</body>
</html>
