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
    <link rel="canonical" href="@yield('canonical', request()->url())">
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
        <div class="backgr_top">
            <div class="content">
                <div class="logo">
                    <a href="{{ route('home') }}"><img src="{{ setting('logotip') }}" alt="{{ setting('title') }}"></a>

                    <div class="ms-auto" style="display:flex; align-items:center; gap:8px">
                        <ul class="hooks-nav">
                            @hook('navbarStart')
                            @hook('navbarEnd')
                        </ul>
                        @if (($user = getUser()) && $user->isActive())
                            <div>
                                <a class="d-flex align-items-center gap-1" href="{{ route('messages.index') }}" aria-label="{{ __('index.private_message') }}"><i class="far fa-envelope fa-lg"></i><span class="badge rounded-pill bg-danger js-message-count">{{ $user->newprivat ?: '' }}</span></a>
                            </div>
                        @endif
                        <div>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#languageModal"><img src="/assets/flags/{{ app()->getLocale() }}.svg" alt="" width="22" class="me-1 flag" onerror="this.remove()"> {{ __('main.lang') }}</a>
                        </div>
                    </div>
                </div>

                <div class="menu">
                    <span class="mright">
                        @include('themes/motor/menu')
                    </span>
                    <ul class="menu-nav">
                        @hook('sidebarMenu')
                    </ul>
                </div>
            </div>
        </div>

        <div class="backgr">
            <div class="bcontent">
                <div class="mcontentwide">
                    @hook('advertTop')

                    @hook('header')

                    @yield('flash')
                    @yield('titlebar')

                    @hook('contentStart')
                    @yield('content')
                    @hook('contentEnd')

                    @hook('advertBottom')

                    <div class="small" id="down">
                        {{ showCounter() }}
                        {{ showOnline() }}
                        {{ performance() }}
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
