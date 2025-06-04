<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>@yield('title') - {{ setting('title') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="image_src" href="/assets/img/images/icon.png">
    <link rel="stylesheet" type="text/css" href="{{ mix('/themes/mobile/dist/app.css') }}">
    @stack('styles')
    <link rel="canonical" href="{{ request()->url() }}">
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="generator" content="Rotor {{ ROTOR_VERSION }}">
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

        @yield('flash')
        @yield('breadcrumb')
        @yield('header')
        @yield('content')
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
<div class="scrollup"></div>
<script src="{{ mix('/assets/js/dist/manifest.js') }}"></script>
<script src="{{ mix('/assets/js/dist/vendor.js') }}"></script>
<script src="{{ mix('/assets/js/dist/lang.js') }}"></script>
<script src="{{ mix('/themes/mobile/dist/app.js') }}"></script>
@stack('scripts')
</body>
</html>
