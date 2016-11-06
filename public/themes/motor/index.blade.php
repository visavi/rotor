<?php
header('Content-type:text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <title>
        @section('title')
            {{ App::setting('title') }}
        @show
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="image_src" href="/assets/img/images/icon.png" />
    @section('styles')
        <?= include_style() ?>
    @show
    @stack('styles')
    <link rel="stylesheet" href="/themes/motor/css/style.css" type="text/css" />
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml" />
    <meta name="description" content="@yield('description', App::setting('description'))">
    <meta name="keywords" content="@yield('keywords', App::setting('keywords'))">
    <meta name="generator" content="RotorCMS {{ App::setting('rotorversion') }}" />
</head>
<body>
<!--Design by Vantuz (http://visavi.net)-->

<div id="wrapper">
    <div class="main" id="up">

        <div class="panelTop">
            <img src="/themes/motor/img/panel_top.gif" alt="" />
        </div>
        <div class="backgr_top">
            <div class="content">
                <div class="logo">
                    <!-- <a href="/"><span class="logotype">{{ App::setting('title') }}</span></a> -->
                    <a href="/"><img src="/assets/img/images/logo.png" alt="{{ App::setting('title') }}" /></a>
                </div>

                <div class="menu">
                    <a href="/forum">Форум</a>
                    <a href="/book">Гостевая</a>
                    <a href="/news">Новости</a>
                    <a href="/load">Скрипты</a>
                    <a href="/blog">Блоги</a>

                    <span class="mright">

<?php if (is_user()): ?>
    <?php if (is_admin()): ?>

        <?php if (stats_spam()>0): ?>
            <a href="/admin/spam"><span style="color:#ff0000">Спам!</span></a>
        <?php endif; ?>

        <?php if (App::user('newchat') < stats_newchat()): ?>
            <a href="/admin/chat"><span style="color:#ff0000">Чат</span></a>
        <?php endif; ?>

            <a href="/admin">Панель</a>
    <?php endif; ?>

    <a href="/menu">Меню</a>
    <a href="/logout" onclick="return confirm('Вы действительно хотите выйти?')">Выход</a>

<?php else: ?>
    <a href="/login">Авторизация</a>/
    <a href="/register">Регистрация</a>
<?php endif; ?>

                    </span>

                </div>
            </div>
        </div>

        <div class="backgr">
            <div class="bcontent">
                <div class="mcontentwide">
<?= render('includes/note'); /*Временно пока шаблоны подключаются напрямую*/ ?>
