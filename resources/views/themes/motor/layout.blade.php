<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
    @vite('resources/themes/motor/js/app.js')
    @stack('styles')
    @hook('head')
</head>
<body>
<!--Design by Vantuz (https://visavi.net)-->

<div id="wrapper">
    <div class="main" id="up">

        <div class="panelTop"></div>
        <div class="backgr_top">
            <div class="content">
                <div class="logo">
                    <a href="{{ route('home') }}"><img src="{{ setting('logotip') }}" alt="{{ setting('title') }}"></a>

                    <div class="ms-auto">
                        <a href="{{ route('language', ['lang' => 'ru']) }}{{ returnUrl() }}">RU</a> /
                        <a href="{{ route('language', ['lang' => 'en']) }}{{ returnUrl() }}">EN</a>
                    </div>
                </div>

                <div class="menu">
                    <span class="mright">
                        @include('themes/motor/menu')
                    </span>
                    <ul class="menu-nav">
                        <li><a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a></li>
                        @if (Route::has('news.index'))
                            <li><a href="{{ route('news.index') }}">{{ __('index.news') }}</a></li>
                        @endif
                        @if (Route::has('loads.index'))
                            <li><a href="{{ route('loads.index') }}">{{ __('index.loads') }}</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="backgr">
            <div class="bcontent">
                <div class="mcontentwide">

                    @yield('advertTop')
                    @yield('advertAdmin')
                    @yield('advertUser')

                    @include('themes/motor/note')
                    @hook('header')

                    @yield('flash')
                    @yield('breadcrumb')
                    @yield('header')

                    @hook('contentStart')
                    @yield('content')
                    @hook('contentEnd')

                    @yield('advertBottom')

                    <div class="small" id="down">
                        @yield('counter')
                        @yield('online')
                        @yield('performance')
                    </div>
                </div>
            </div>

            <div class="footer">
                <div class="footer-text">
                    {{ setting('copy') }}
                </div>
            </div>
            <div class="panelBot"></div>
        </div>
    </div>
</div>
@stack('scripts')
<div class="scrollup"></div>
@hook('footer')
</body>
</html>
