<?php
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>
        @section('title')
            {{ setting('title') }}
        @show
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="image_src" href="/assets/img/images/icon.png">
    @section('styles')
        <?= includeStyle() ?>
    @show
    @stack('styles')
    <link rel="stylesheet" href="/themes/default/css/style.css">
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml">

    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="keywords" content="@yield('keywords', setting('keywords'))">
    <meta name="generator" content="RotorCMS {{ env('VERSION') }}">
</head>
<body>

<div class="cs" id="up">
    <!-- <a href="/"><span class="logotype">{{ setting('title') }}</span></a><br> -->
    <a href="/"><img src="{{ setting('logotip') }}" alt="{{ setting('title') }}"></a><br>
    {{ setting('logos') }}
</div>

<?php view('app/_menu'); ?>

<div class="site">
<?= view('app/_note'); /*Временно пока шаблоны подключаются напрямую*/ ?>
