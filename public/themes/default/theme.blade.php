<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <meta charset="utf-8">
    <meta name="theme-color" content="#2e8cc2">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="generator" content="Rotor {{ VERSION }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ setting('title') }}</title>
    <link href="/favicon.ico" rel="icon">
    <link href="/assets/img/images/icon.png" rel="image_src">
    <link href="/news/rss" title="RSS News" type="application/rss+xml" rel="alternate">
    <link rel="stylesheet" type="text/css" href="{{ mix('/themes/default/dist/app.css') }}">
    @stack('styles')
</head>
<body class="app sidebar">

@include('navbar')
@include('sidebar')

<main class="app-content">
    <div class="app-title">
        @yield('header')
        @yield('breadcrumb')
    </div>

{{--    <div class="app-title">
        <div>
            <h1><i class="fa fa-file-code-o"></i> Documentation</h1>
            <p>Documentation of vali admin</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item"><a href="#">Documentation</a></li>
        </ul>
    </div>--}}
    {{--<div class="tile">--}}
        @yield('flash')
        @yield('advertTop')
        @yield('advertAdmin')
        @yield('advertUser')

        @yield('content')
        @yield('advertBottom')
    {{--</div>--}}
    @include('footer')
</main>
<script src="{{ mix('/assets/js/dist/manifest.js') }}"></script>
<script src="{{ mix('/assets/js/dist/vendor.js') }}"></script>
<script src="{{ mix('/assets/js/dist/lang.js') }}"></script>
<script src="{{ mix('/themes/default/dist/app.js') }}"></script>
@stack('scripts')
<div class="scrollup"></div>
</body>
</html>
