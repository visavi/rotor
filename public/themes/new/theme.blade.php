<!doctype html>
<html lang="{{ setting('language') }}">
<head>
    <meta charset="utf-8">
    <meta name="theme-color" content="#2e8cc2">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="generator" content="Rotor {{ VERSION }}">
    <title>@yield('title') - {{ setting('title') }}</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link href="/favicon.ico" rel="icon">
    <link href="/assets/img/images/icon.png" rel="image_src">
    <link href="/news/rss" title="RSS News" type="application/rss+xml" rel="alternate">
    @yield('styles')
    @stack('styles')
    <link href="/themes/new/assets/css/custom.css" rel="stylesheet">
</head>

<body>
<div class="app">
    @include('sidebar')
    <div class="app-content">

        <div class="content-header">
            @include('navbar')
            @yield('breadcrumb')
        </div>

        <div class="content-body">
            <div class="container">
                @yield('advertTop')
                @yield('advertAdmin')
                @yield('advertUser')
                @yield('note')
                @yield('flash')
                @yield('header')
                @yield('content')
                @yield('advertBottom')
            </div>
        </div>
    </div>
</div>

<div class="scrollup"></div>
@yield('scripts')
@stack('scripts')
<script src="/themes/new/assets/js/custom.js"></script>
</body>
</html>
