<?php
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>@yield('title') - {{ setting('title') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="image_src" href="/assets/img/images/icon.png">
    @yield('styles')
    @stack('styles')
    <link rel="stylesheet" href="/themes/default/css/style.css">
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="keywords" content="@yield('keywords', setting('keywords'))">
    <meta name="generator" content="Rotor {{ VERSION }}">
</head>
<body>

<div class="cs" id="up">
    {{--<a href="/"><span class="logotype">{{ setting('title') }}</span></a><br>--}}
    <a href="/"><img src="{{ setting('logotip') }}" alt="{{ setting('title') }}"></a><br>
    {{ setting('logos') }}
</div>

<div class="menu">
@yield('menu')
</div>

<div class="site">
    <div class="content">
        @yield('advertTop')
        @yield('advertUser')
        @yield('note')
        @yield('flash')
        @yield('layout')
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

@yield('scripts')
@stack('scripts')
</body>
</html>
