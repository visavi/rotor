<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="{{ request()->cookie('theme') ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2e8cc2">
    <meta name="generator" content="Rotor {{ ROTOR_VERSION }}">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="image" content="{{ asset('/assets/img/images/icon.png') }}">
    <title>@yield('title') - {{ setting('title') }}</title>
    <link rel="canonical" href="@yield('canonical', request()->url())">
    <link rel="icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/assets/img/images/icon.svg">
    <link rel="icon" type="image/png" href="/assets/img/images/icon.png" sizes="128x128">
    <link rel="apple-touch-icon" href="/assets/img/images/apple-touch-icon.png">
    <link href="{{ route('news.rss') }}" title="RSS News" type="application/rss+xml" rel="alternate">
    <link rel="stylesheet" type="text/css" href="{{ mix('/assets/dist/css/default.css') }}">
    @stack('styles')
    @hook('head')
</head>
<body class="app">

@include('navbar')
@include('sidebar')

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

@include('footer')
<script src="{{ mix('/assets/dist/js/manifest.js') }}"></script>
<script src="{{ mix('/assets/dist/js/vendor.js') }}"></script>
<script src="{{ mix('/assets/dist/js/lang.js') }}"></script>
<script src="{{ mix('/assets/dist/js/default.js') }}"></script>
{{--@if (getUser())
<script src="{{ mix('/assets/dist/js/messages.js') }}"></script>
@endif--}}
@stack('scripts')
@hook('footer')
<div class="scrollup"></div>
</body>
</html>
