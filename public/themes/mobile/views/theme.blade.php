<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="generator" content="Rotor {{ ROTOR_VERSION }}">
    <meta name="image" content="{{ asset('/assets/img/images/icon.png') }}">
    <title>@yield('title') - {{ setting('title') }}</title>
    <link rel="canonical" href="@yield('canonical', request()->url())">
    <link rel="icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/assets/img/images/icon.svg">
    <link rel="icon" type="image/png" href="/assets/img/images/icon.png" sizes="128x128">
    <link rel="apple-touch-icon" href="/assets/img/images/apple-touch-icon.png">
    <link rel="alternate" href="{{ route('news.rss') }}" title="RSS News" type="application/rss+xml">
    <link rel="stylesheet" type="text/css" href="{{ mix('/assets/dist/css/mobile.css') }}">
    @stack('styles')
    @hook('head')
</head>
<body>

<div class="cs" id="up">
    {{--<a href="/"><span class="logotype">{{ setting('title') }}</span></a><br>--}}
    <a href="/"><img src="{{ setting('logotip') }}" alt="{{ setting('title') }}"></a><br>
    {{ setting('logos') }}
</div>

<div class="menu">
    @include('menu')

    <div class="float-end">
        <a href="/language/ru{{ returnUrl() }}">RU</a> /
        <a href="/language/en{{ returnUrl() }}">EN</a>
    </div>
</div>

<div class="site">
    <div class="content">
        @yield('advertTop')
        @yield('advertAdmin')
        @yield('advertUser')

        @include('note')
        @hook('header')

        @yield('flash')
        @yield('breadcrumb')
        @yield('header')

        @hook('contentStart')
        @yield('content')
        @hook('contentEnd')

        @yield('advertBottom')
    </div>
</div>

<div class="lol" id="down">
    <a href="/">{{ setting('copy') }}</a><br>
    @yield('online')
    @yield('counter')
</div>
<div class="site" style="text-align:center">
    @yield('performance')
</div>
<script src="{{ mix('/assets/dist/js/manifest.js') }}"></script>
<script src="{{ mix('/assets/dist/js/vendor.js') }}"></script>
<script src="{{ mix('/assets/dist/js/lang.js') }}"></script>
<script src="{{ mix('/assets/dist/js/mobile.js') }}"></script>
@stack('scripts')
@hook('footer')
<div class="scrollup"></div>
</body>
</html>
