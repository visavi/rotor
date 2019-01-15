<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="keywords" content="@yield('keywords', setting('keywords'))">
    <meta name="generator" content="Rotor {{ VERSION }}">

    <title>@yield('title') - {{ setting('title') }}</title>

    <link rel="icon" href="/favicon.ico">
    <link rel="image_src" href="/assets/img/images/icon.png">
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml">
    @yield('styles')
    @stack('styles')
    <link rel="stylesheet" href="/themes/test/css/style.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg bg-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/"><img src="/assets/img/images/logo.png" alt="{{ setting('title') }}"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        @include('./menu')
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-8 mt-4">
            @yield('advertTop')
            @yield('advertUser')
            @yield('note')
            @yield('flash')
            @yield('layout')
            @yield('advertBottom')
        </div>

        <!-- Sidebar Widgets Column -->
        <div class="col-md-4">
            <!-- Search Widget -->
            <div class="card my-4">
                <h5 class="card-header">Search</h5>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search for...">
                        <span class="input-group-btn">
                            <button class="btn btn-secondary" type="button">Go!</button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card my-4">
                <h5 class="card-header">Мое меню</h5>
                <div class="card-body">
                    @if (getUser())
                        @include('main/menu')
                    @else
                        @include('main/recent')
                    @endif
                </div>
            </div>

            <!-- Categories Widget -->
            <div class="card my-4">
                <h5 class="card-header">Categories</h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <ul class="list-unstyled mb-0">
                                <li>
                                    <a href="#">Web Design</a>
                                </li>
                                <li>
                                    <a href="#">HTML</a>
                                </li>
                                <li>
                                    <a href="#">Freebies</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <ul class="list-unstyled mb-0">
                                <li>
                                    <a href="#">JavaScript</a>
                                </li>
                                <li>
                                    <a href="#">CSS</a>
                                </li>
                                <li>
                                    <a href="#">Tutorials</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Widget -->
            <div class="card my-4">
                <h5 class="card-header">Side Widget</h5>
                <div class="card-body">
                    You can put anything you want inside of these side widgets. They are easy to use, and feature the new Bootstrap 4 card containers!
                </div>
            </div>

        </div>
    </div>
</div>

<footer class="py-5 bg-light">
    <div class="container">
        <p class="m-0 text-center text-white">&copy; Copyright 2005-{{ date('Y') }} {{ setting('title') }}</p>

        @yield('online')
        @yield('counter')
        @yield('performance')
    </div>
</footer>

@yield('scripts')
@stack('scripts')
</body>
</html>
