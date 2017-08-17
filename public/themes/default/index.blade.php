<?php
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
<head>
    <title>
        @section('title')
            {{ Setting::get('title') }}
        @show
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="image_src" href="/assets/img/images/icon.png">
    @section('styles')
        <?= include_style() ?>
    @show
    @stack('styles')
    <link rel="stylesheet" href="/themes/default/css/style.css">
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml">

    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="@yield('description', Setting::get('description'))">
    <meta name="keywords" content="@yield('keywords', Setting::get('keywords'))">
    <meta name="generator" content="RotorCMS {{ env('VERSION') }}">
</head>
<body>

<div class="cs" id="up">
    <!-- <a href="/"><span class="logotype">{{ Setting::get('title') }}</span></a><br> -->
    <a href="/"><img src="{{ Setting::get('logotip') }}" alt="{{ Setting::get('title') }}"></a><br>
    {{ Setting::get('logos') }}
</div>

<?php App::view('includes/menu'); ?>

<div class="site">
<?= App::view('includes/note'); /*Временно пока шаблоны подключаются напрямую*/ ?>
