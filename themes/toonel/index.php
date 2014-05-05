<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <title>%TITLE%</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="/themes/toonel/css/style.css" type="text/css"/>
    <link rel="alternate" href="/news/rss.php" title="RSS News" type="application/rss+xml"/>
    <?= include_javascript() ?>
    <meta name="keywords" content="%KEYWORDS%"/>
    <meta name="description" content="%DESCRIPTION%"/>
    <meta name="generator" content="RotorCMS <?= $config['rotorversion'] ?>"/>
</head>
<body>
<!--Design by Vantuz (http://pizdec.ru)-->
<table border="0" align="center" cellpadding="0" cellspacing="0" class="submenu" id="up">
    <tr>
        <td class="t1">
            <a href="/">
                <img src="/themes/toonel/img/logo.gif" alt="<?= $config['title'] ?>"/>
            </a>
        </td>
        <td class="t2"></td>
        <td class="t3">
            <a title="Центр общения" class="menu" href="/forum/?">Форум</a> |
            <a title="Гостевая комната" class="menu" href="/book/?">Гостевая</a> |
            <a title="Скрипты для wap-мастеров" class="menu" href="/load/?">Скрипты</a> |
            <?php if (is_user()): ?>
                <a title="Управление настройками" class="menu" href="/pages/?act=menu">Мое меню</a> |
                <a title="Выход" class="menu" href="/input.php?act=exit"
                   onclick="return confirm('Вы действительно хотите выйти?')">Выход</a>
            <?php else: ?>
                <a title="Страница авторизации" class="menu" href="/pages/login.php?">Вход</a> |
                <a title="Страница регистрации" class="menu"
                   href="/pages/registration.php?">Регистрация</a>
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
                    <img src="/images/img/panel.gif" alt="panel"/> <a
                        href="/admin/index.php?">Панель</a>

                    <?php if (stats_spam() > 0): ?>
                        &bull; <a href="/admin/spam.php?"><span style="color:#ff0000">Спам!</span></a>
                    <?php endif; ?>

                    <?php if ($udata['users_newchat'] < stats_newchat()): ?>
                        &bull; <a href="/admin/chat.php?"><span style="color:#ff0000">Чат</span></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php render('includes/note', array('php_self' => $php_self)); ?>

            <div>
