<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <title>@yield('title') - {{ setting('title') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="/favicon.ico">
    <link rel="image_src" href="/assets/img/images/icon.png">
    <link rel="stylesheet" type="text/css" href="{{ mix('/themes/motor/dist/app.css') }}">
    @stack('styles')
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="generator" content="Rotor {{ VERSION }}">
</head>
<body>
<!--Design by Vantuz (https://visavi.net)-->

<div id="wrapper">
    <div class="main" id="up">

        <div class="panelTop">
            <img src="/themes/motor/src/img/panel_top.gif" alt="">
        </div>
        <div class="backgr_top">
            <div class="content">
                <div class="logo">
                    <!-- <a href="/"><span class="logotype">{{ setting('title') }}</span></a> -->
                    <a href="/"><img src="/assets/img/images/logo.png" alt="{{ setting('title') }}"></a>

                    <div class="float-right m-3">
                        <a href="/language/ru{{ returnUrl() }}">RU</a> /
                        <a href="/language/en{{ returnUrl() }}">EN</a>
                    </div>
                </div>

                <div class="menu">
                    <a href="/forums">{{ __('index.forums') }}</a> &bull;
                    <a href="/guestbook">{{ __('index.guestbook') }}</a> &bull;
                    <a href="/news">{{ __('index.news') }}</a> &bull;
                    <a href="/loads">{{ __('index.loads') }}</a> &bull;
                    <a href="/blogs">{{ __('index.blogs') }}</a>

                    <span class="mright">
                        @include('menu')
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

                    @include('note')

                    @yield('flash')
                    @yield('breadcrumb')
                    @yield('header')
                    @yield('content')
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
                    <a href="/"><img src="/themes/motor/src/img/smalllogo2.gif" alt="smalllogo"></a>
                </div>
            </div>
            <img src="/themes/motor/src/img/panel_bot.gif" alt="">
        </div>
    </div>
</div>
<div class="scrollup"></div>
<script src="{{ mix('/assets/js/dist/manifest.js') }}"></script>
<script src="{{ mix('/assets/js/dist/vendor.js') }}"></script>
<script src="{{ mix('/assets/js/dist/lang.js') }}"></script>
<script src="{{ mix('/themes/motor/dist/app.js') }}"></script>
@stack('scripts')
</body>
</html>
