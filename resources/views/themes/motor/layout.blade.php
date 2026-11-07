<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="generator" content="Rotor {{ ROTOR_VERSION }}">
    <meta name="image" content="{{ asset('/assets/img/images/icon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('app/_meta_og')
    <title>@yield('title') - {{ setting('title') }}</title>
    <link rel="canonical" href="@yield('canonical', canonicalUrl())">
    <link rel="icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/assets/img/images/icon.svg">
    <link rel="icon" type="image/png" href="/assets/img/images/icon.png" sizes="128x128">
    <link rel="apple-touch-icon" href="/assets/img/images/apple-touch-icon.png">
    @translation
    @vite('resources/css/bootstrap.scss')
    @vite('resources/themes/vendor.scss')
    @vite('resources/themes/motor/js/app.js')
    @stack('styles')
    @hook('head')
</head>
<body>
<!--Design by Vantuz (https://visavi.net)-->

<div id="wrapper">
    <div class="main" id="up">

        <div class="panelTop"></div>
        @yield('navbar')

        <div class="backgr">
            <div class="bcontent">
                <div class="mcontentwide">
                    @section('advertTop')
                        @hook('advertTop')
                    @show

                    @hook('header')

                    @yield('flash')
                    @yield('titlebar')

                    @hook('contentStart')
                    @yield('content')
                    @hook('contentEnd')

                    @section('advertBottom')
                        @hook('advertBottom')
                    @show

                    @yield('footer')
                </div>
            </div>

            {{-- Копирайт стоит вне контента, но гаснет вместе с футером на простых страницах --}}
            @hasSection('footer')
                <div class="footer">
                    <div class="footer-text">
                        {{ setting('copy') }}
                    </div>
                </div>
            @endif
            <div class="panelBot"></div>
        </div>
    </div>
</div>
@stack('scripts')
<div class="scrollup"></div>
@hook('footer')
</body>
</html>
