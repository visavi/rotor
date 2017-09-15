<?php
header('Content-type:text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>@yield('title') {{ setting('title') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="/favicon.ico">
    <link rel="image_src" href="/assets/img/images/icon.png">
    @yield('styles')
    @stack('styles')
    <link rel="stylesheet" href="/themes/motor/css/style.css">
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="keywords" content="@yield('keywords', setting('keywords'))">
    <meta name="generator" content="RotorCMS {{ env('VERSION') }}">
</head>
<body>
<!--Design by Vantuz (http://visavi.net)-->

<div id="wrapper">
    <div class="main" id="up">

        <div class="panelTop">
            <img src="/themes/motor/img/panel_top.gif" alt="">
        </div>
        <div class="backgr_top">
            <div class="content">
                <div class="logo">
                    <!-- <a href="/"><span class="logotype">{{ setting('title') }}</span></a> -->
                    <a href="/"><img src="/assets/img/images/logo.png" alt="{{ setting('title') }}"></a>
                </div>

                <div class="menu">
                    <a href="/forum">Форум</a> &bull;
                    <a href="/book">Гостевая</a> &bull;
                    <a href="/news">Новости</a> &bull;
                    <a href="/load">Скрипты</a> &bull;
                    <a href="/blog">Блоги</a>

                    <span class="mright">
                        @yield('menu')
                    </span>
                </div>
            </div>
        </div>

        <div class="backgr">
            <div class="bcontent">
                <div class="mcontentwide">

                    @yield('advertTop')
                    @yield('advertUser')
                    @yield('note')
                    @yield('flash')
                    @yield('layout')
                    @yield('advertBottom')

                    <div class="small" id="down">
                        @yield('counter')
                        @yield('online')
                        @yield('performance')
                    </div>
                </div>
            </div>

            <div id="footer">
                <div id="text">
                    &copy; Copyright 2005-{{ date('Y') }} {{ setting('title') }}
                </div>
                <div id="image">
                    <a href="/"><img src="/themes/motor/img/smalllogo2.gif" alt="smalllogo"></a>
                </div>
            </div>
            <img src="/themes/motor/img/panel_bot.gif" alt="">
        </div>
    </div>
</div>
@yield('scripts')
@stack('scripts')
</body>
</html>
