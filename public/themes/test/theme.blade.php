<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="">
    <meta name="description" content="@yield('description', setting('description'))">
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
<nav class="navbar navbar-expand-md navbar-dark bg-site fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/"><img src="{{ setting('logotip') }}" alt="{{ setting('title') }}"></a>

        @if ($user = getUser())
            <div class="float-right">
                <ul class="navbar-nav flex-row">
                    <li class="nav-item">
                        <a class="nav-link tools-item" href="/messages">
                            <i class="fas fa-bell"></i>
                            @if ($user->newprivat)
                                <i class="tools-item-count">{{ $user->newprivat }}</i>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link tools-item" href="/walls/{{ getUser('login') }}">
                            <i class="fas fa-comment"></i>
                            @if ($user->newwall)
                                <i class="tools-item-count">{{ $user->newwall }}</i>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        @include('navbar')
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-8 mt-4">
            @yield('advertTop')
            @yield('advertAdmin')
            @yield('advertUser')
            {{--@yield('note')--}}
            @yield('flash')
            @yield('breadcrumb')
            @yield('header')
            @yield('content')
            @yield('advertBottom')
        </div>

        @include('sidebar')
    </div>
</div>

<footer class="py-5 bg-secondary">
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
