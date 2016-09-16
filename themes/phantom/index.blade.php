<?php
header('Content-type:text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>
            @section('title')
            {{ App::setting('title') }}
            @show
        </title>
        <meta charset="utf-8" />
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
        <link rel="image_src" href="/images/img/icon.png" />
        <link rel="alternate" href="/news/rss.php" title="RSS News" type="application/rss+xml" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="keywords" content="@yield('keywords', App::setting('keywords'))" />
        <meta name="description" content="@yield('description', App::setting('description'))" />
        <meta name="generator" content="RotorCMS {{ App::setting('rotorversion') }}" />
        @section('styles')
            <?= include_style() ?>
        @show
        @stack('styles')
        <!--[if lte IE 8]><script src="/themes/phantom/js/ie/html5shiv.js"></script><![endif]-->
        <link rel="stylesheet" href="/themes/phantom/css/main.css" />
        <!--[if lte IE 9]><link rel="stylesheet" href="/themes/phantom/css/ie9.css" /><![endif]-->
        <!--[if lte IE 8]><link rel="stylesheet" href="/themes/phantom/css/ie8.css" /><![endif]-->
    </head>
    <body>
        <!-- Wrapper -->
            <div id="wrapper">

                <!-- Header -->
                    <header id="header">
                        <div class="inner">

                            <!-- Logo -->
                                <a href="/" class="logo">
                                    <span class="symbol"><img src="/images/img/icon.png" alt="{{ App::setting('title') }}" /></span><span class="title">{{ App::setting('title') }}</span>
                                </a>

                            <!-- Nav -->
                                <nav>
                                    <ul>
                                        <li><a href="#menu">Меню</a></li>
                                    </ul>
                                </nav>

                        </div>
                    </header>

                    @include('/phantom/menu')

                    <div id="main">
                        <div class="inner">
<?= render('includes/note'); /*Временно пока шаблоны подключаются напрямую*/ ?>
