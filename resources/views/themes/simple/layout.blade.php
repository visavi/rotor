{{--
    Тема Simple — пример самодостаточной темы.

    Не требует npm и сборки: свои стили лежат обычными файлами в public/themes/simple,
    а общие библиотеки (Bootstrap JS, FontAwesome, fancybox, tiptap, notyf) берутся
    из общих сборок ядра — resources/themes/vendor.scss и resources/themes/app.js.
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="{{ request()->cookie('theme') ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d7a5f">
    <meta name="generator" content="Rotor {{ ROTOR_VERSION }}">
    <meta name="description" content="@yield('description', setting('description'))">
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
    @vite('resources/js/main.js')
    {{-- Тема не собирается, хеша в имени нет — версию берём из времени правки файла,
         иначе правленые стили не долетают до тех, у кого статика закеширована --}}
    <link rel="stylesheet" href="{{ asset('/themes/simple/style.css') }}?v={{ filemtime(public_path('themes/simple/style.css')) }}">
    <script src="{{ asset('/themes/simple/app.js') }}?v={{ filemtime(public_path('themes/simple/app.js')) }}" defer></script>
    @stack('styles')
    @hook('head')
</head>
<body class="app">

@yield('navbar')

<main class="app-content wrap">
    @yield('titlebar')
    @yield('flash')

    <div class="mb-2">
        @hook('advertTop')
    </div>

    @hook('contentStart')
    @yield('content')
    @hook('contentEnd')
    @hook('advertBottom')
</main>

@include('themes/simple/footer')
@stack('scripts')
<div class="scrollup"></div>
@hook('footer')
</body>
</html>
