<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <title>@yield('title') - {{ setting('title') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="image_src" href="/assets/img/images/icon.png">
    @yield('styles')
    @stack('styles')
    <link rel="stylesheet" href="/themes/bluewater/css/style.css">
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml">
    <meta name="keywords" content="%KEYWORDS%">
    <meta name="description" content="%DESCRIPTION%">
    <meta name="generator" content="Rotor {{ VERSION }}">
</head>
<body>
<!--Design by WmLiM (http://komwap.ru)-->

<div id="wrap">
    <div id="header">
        <h1 id="logo-text"><a href="/">{{ setting('title') }}</a></h1>
        <p id="slogan">{{ setting('logos') }}</p>

        <div id="header-links">
            <div class="menu">
                @yield('menu')
            </div>
        </div>
    </div>

    <!-- navigation -->
    <div id="menu">
        <ul>
            <li><a href="/">Главная</a></li>
            <li><a href="/forums">Форум</a></li>
            <li><a href="/loads">Загрузки</a></li>
            <li><a href="/blogs">Блоги</a></li>
            <li><a href="/photos">Галерея</a></li>
        </ul>
    </div>

    <!-- content-wrap starts here -->
    <div id="content-wrap">
        <div id="sidebar">

            @if (getUser())
                @include('main/menu')
            @else
                @include('main/recent')
            @endif
        </div>
        <div id="main">
            <div class="body_center">
                @yield('advertTop')
                @yield('advertUser')
                @yield('note')
                @yield('flash')
                @yield('breadcrumb')
                @yield('header')
                @yield('content')
                @yield('advertBottom')
            </div>
        </div>
    </div>

    <!--footer starts here-->
    <div id="footer">

        <a href="/">{{ setting('copy') }}</a><br>
        @yield('online')
        @yield('counter')
        @yield('performance')
    </div>
</div>

@yield('scripts')
@stack('scripts')
</body>
</html>

