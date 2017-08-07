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
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>
    <?= include_style() ?>
    <link rel="stylesheet" href="/themes/toonel/css/style.css" type="text/css"/>
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml"/>
    <?= include_javascript() ?>
    <meta name="keywords" content="%KEYWORDS%"/>
    <meta name="description" content="%DESCRIPTION%"/>
    <meta name="generator" content="RotorCMS <?= env('VERSION') ?>"/>
</head>
<body>
<!--Design by Vantuz (http://pizdec.ru)-->
<table border="0" align="center" cellpadding="0" cellspacing="0" class="submenu" id="up">
    <tr>
        <td class="t1">
            <a href="/">
                <img src="/themes/toonel/img/logo.gif" alt="<?= Setting::get('title') ?>"/>
            </a>
        </td>
        <td class="t2"></td>
        <td class="t3">
            <a title="Центр общения" class="menu" href="/forum">Форум</a> |
            <a title="Гостевая комната" class="menu" href="/book">Гостевая</a> |
            <a title="Скрипты для wap-мастеров" class="menu" href="/load">Скрипты</a> |
            <?php if (is_user()): ?>
                <a title="Управление настройками" class="menu" href="/menu">Меню</a> |
                <a title="Выход" class="menu" href="/logout"
                   onclick="return confirm('Вы действительно хотите выйти?')">Выход</a>
            <?php else: ?>
                <a title="Страница авторизации" class="menu" href="/login">Вход</a> |
                <a title="Страница регистрации" class="menu"
                   href="/register">Регистрация</a>
            <?php endif ?>
        </td>
        <td class="t4"></td>
    </tr>
</table>

<table border="0" align="center" cellpadding="0" cellspacing="0" class="tab2">
    <tr>
        <td align="left" valign="top" class="leftop">
        </td>
        <td class="bortop"></td>
        <td align="right" valign="top" class="righttop"></td>
    </tr>
    <tr>
        <td class="left_mid">&nbsp;</td>
        <td valign="top" class="lr">
            <?php if (is_admin()): ?>
                <div class="nmenu">
                    <i class="fa fa-wrench"></i> <a
                        href="/admin">Панель</a>

                    <?php if (stats_spam() > 0): ?>
                        &bull; <a href="/admin/spam"><span style="color:#ff0000">Спам!</span></a>
                    <?php endif; ?>

                    <?php if (App::user('newchat') < stats_newchat()): ?>
                        &bull; <a href="/admin/chat"><span style="color:#ff0000">Чат</span></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div>
<?= App::view('includes/note'); /*Временно пока шаблоны подключаются напрямую*/ ?>
