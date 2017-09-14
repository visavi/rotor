<?php
header('Content-type:text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>
        @section('title')
            {{ setting('title') }}
        @show
    </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="/favicon.ico">
    <link rel="image_src" href="/assets/img/images/icon.png">
    @section('styles')
        <?= includeStyle() ?>
    @show
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
                    <a href="/forum">Форум</a>
                    <a href="/book">Гостевая</a>
                    <a href="/news">Новости</a>
                    <a href="/load">Скрипты</a>
                    <a href="/blog">Блоги</a>

                    <span class="mright">

<?php if (isUser()): ?>
    <?php if (isAdmin()): ?>

        <?php if (statsSpam()>0): ?>
            <a href="/admin/spam"><span style="color:#ff0000">Спам!</span></a>
        <?php endif; ?>

        <?php if ( user('newchat') < statsNewChat()): ?>
            <a href="/admin/chat"><span style="color:#ff0000">Чат</span></a>
        <?php endif; ?>

            <a href="/admin">Панель</a>
    <?php endif; ?>

    <a href="/menu">Меню</a>
    <a href="/logout" onclick="return confirm('Вы действительно хотите выйти?')">Выход</a>

<?php else: ?>
    <a href="/login{{ returnUrl() }}">Авторизация</a>/
    <a href="/register">Регистрация</a>
<?php endif; ?>

                    </span>

                </div>
            </div>
        </div>

        <div class="backgr">
            <div class="bcontent">
                <div class="mcontentwide">

                    @yield('layout')

                    <div class="small" id="down">
                        <?= showCounter() ?>
                        <?= showOnline() ?>
                        <?= perfomance() ?>
                    </div>
                </div>
            </div>

            <div id="footer">
                <div id="text">
                    &copy; Copyright 2005-<?=date('Y')?> {{ setting('title') }}
                </div>
                <div id="image">
                    <a href="/"><img src="/themes/motor/img/smalllogo2.gif" alt="smalllogo"></a>
                </div>
            </div>
            <img src="/themes/motor/img/panel_bot.gif" alt="">
        </div>
    </div>
</div>
@section('scripts')
    <?= includeScript() ?>
@show
@stack('scripts')
</body>
</html>
