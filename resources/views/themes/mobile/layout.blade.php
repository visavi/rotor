<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="generator" content="Rotor {{ ROTOR_VERSION }}">
    <meta name="image" content="{{ asset('/assets/img/images/icon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ setting('title') }}</title>
    <link rel="canonical" href="@yield('canonical', request()->url())">
    <link rel="icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/assets/img/images/icon.svg">
    <link rel="icon" type="image/png" href="/assets/img/images/icon.png" sizes="128x128">
    <link rel="apple-touch-icon" href="/assets/img/images/apple-touch-icon.png">
    @vite('resources/themes/vendor.scss')
    @vite('resources/themes/mobile/js/app.js')
    @stack('styles')
    @hook('head')
</head>
<body>

<div class="cs" id="up">
    {{--<a href="{{ route('home') }}"><span class="logotype">{{ setting('title') }}</span></a><br>--}}
    <a href="{{ route('home') }}"><img src="{{ setting('logotip') }}" alt="{{ setting('title') }}"></a><br>
    {{ setting('logos') }}
</div>

<div class="menu">
    @include('themes/mobile/menu')

    <div class="float-end" style="display:flex; align-items:center; gap:8px">
        <ul class="hooks-nav">
            @hook('navbarStart')
            @hook('navbarEnd')
        </ul>
        <div>
            <a href="{{ route('language', ['lang' => 'ru']) }}{{ returnUrl() }}">RU</a> /
            <a href="{{ route('language', ['lang' => 'en']) }}{{ returnUrl() }}">EN</a>
        </div>
    </div>
</div>

<div class="site">
    <div class="content">
        @hook('advertTop')

        @include('themes/motor/note')
        @hook('header')

        @yield('flash')
        @yield('breadcrumb')
        @yield('header')

        @hook('contentStart')
        @yield('content')
        @hook('contentEnd')

        @hook('advertBottom')
    </div>
</div>

<div class="lol" id="down">
    <a href="{{ route('home') }}">{{ setting('copy') }}</a><br>
    {{ showOnline() }}
    {{ showCounter() }}
</div>
<div class="site" style="text-align:center">
    {{ performance() }}
</div>
@stack('scripts')
<div class="scrollup"></div>
@hook('footer')
</body>
</html>
