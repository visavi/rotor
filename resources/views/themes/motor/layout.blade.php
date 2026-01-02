<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
    @vite('public/assets/themes/motor/sass/app.scss')
    @stack('styles')
    @hook('head')
</head>
<body>
<!--Design by Vantuz (https://visavi.net)-->

<div id="wrapper">
    <div class="main" id="up">

        <div class="panelTop">
            <img src="/assets/themes/motor/img/panel_top.gif" alt="">
        </div>
        <div class="backgr_top">
            <div class="content">
                <div class="logo">
                    <!-- <a href="/"><span class="logotype">{{ setting('title') }}</span></a> -->
                    <a href="/"><img src="/assets/img/images/logo.png" alt="{{ setting('title') }}"></a>

                    <div class="float-end m-3">
                        <a href="/language/ru{{ returnUrl() }}">RU</a> /
                        <a href="/language/en{{ returnUrl() }}">EN</a>
                    </div>
                </div>

                <div class="menu">
                    <a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a> &bull;
                    <a href="{{ route('guestbook.index') }}">{{ __('index.guestbook') }}</a> &bull;
                    <a href="{{ route('news.index') }}">{{ __('index.news') }}</a> &bull;
                    <a href="{{ route('loads.index') }}">{{ __('index.loads') }}</a> &bull;
                    <a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a>

                    <span class="mright">
                        @include('themes/motor/menu')
                    </span>
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
                <div class="footer-image">
                    <a href="/"><img src="/assets/themes/motor/img/smalllogo2.gif" alt="smalllogo"></a>
                </div>
            </div>
            <img src="/assets/themes/motor/img/panel_bot.gif" alt="">
        </div>
    </div>
</div>
@vite('public/assets/themes/motor/js/app.js')
@stack('scripts')
@hook('footer')
<div class="scrollup"></div>
</body>
</html>
