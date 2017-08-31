<?php
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>
        @section('title')
            {{ setting('title') }}
        @show
    </title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<?= include_style(); ?>
<link rel="stylesheet" href="/themes/sky/css/style.css" media="screen">
<link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml">
<?= include_javascript(); ?>
<meta name="keywords" content="@yield('keywords', setting('keywords'))">
<meta name="description" content="@yield('description', setting('description'))">
<meta name="generator" content="RotorCMS <?= env('VERSION') ?>">
</head><body>
<!--Themes by TurikUs-->

<div id="art-page-background-simple-gradient">
        <div id="art-page-background-gradient"></div>
    </div>
    <div id="art-page-background-glare">
        <div id="art-page-background-glare-image"></div>
    </div>
    <div id="art-main">
        <div class="art-Sheet">
            <div class="art-Sheet-tl"></div>
            <div class="art-Sheet-tr"></div>
            <div class="art-Sheet-bl"></div>
            <div class="art-Sheet-br"></div>
            <div class="art-Sheet-tc"></div>
            <div class="art-Sheet-bc"></div>
            <div class="art-Sheet-cl"></div>
            <div class="art-Sheet-cr"></div>
            <div class="art-Sheet-cc"></div>
            <div class="art-Sheet-body">
                <div class="art-nav">
                	<div class="l"></div>
                	<div class="r"></div>
                	<ul class="art-menu">
                		<li><a href="/"><span class="l"></span><span class="r"></span><span class="t">Главная</span></a></li>
                		<li><a href="/forum"><span class="l"></span><span class="r"></span><span class="t">Форум</span></a>
                           <ul>
                                 <li><a href="/forum/new/themes">Новые темы</a></li>
                                 <li><a href="/forum/new/posts">Новые сообщения</a></li>
                		   </ul></li>



                		<li><a href="/book"><span class="l"></span><span class="r"></span><span class="t">Гостевая</span></a></li>

                        <li><a href="/load"><span class="l"></span><span class="r"></span><span class="t">Файлы</span></a>
                           <ul>
                                 <li><a href="/load/new?act=files">Новые файлы</a></li>
                                 <li><a href="/load/new?act=comments">Новые комментарии</a></li>
                		   </ul></li>

                        <li><a href="/blog"><span class="l"></span><span class="r"></span><span class="t">Блоги</span></a>
                           <ul>
                                 <li><a href="/blog/new?act=blogs">Новые статьи</a></li>
                                 <li><a href="/blog/new?act=comments">Новые комментарии</a></li>
                		   </ul></li>


                        <li><a href="/gallery"><span class="l"></span><span class="r"></span><span class="t">Галерея</span></a>
                           <ul>
                                 <li><a href="/gallery/top">Топ фото</a></li>
                                 <li><a href="/gallery/albums">Все альбомы</a></li>
											<li><a href="/gallery/comments">Все комментарии</a></li>
                		   </ul></li>


                		<li><a href="#"><span class="l"></span><span class="r"></span><span class="t">Актив сайта</span></a>
                           <ul>
                                 <li><a href="/adminlist">Администрация</a></li>
                                 <li><a href="/userlist">Пользователи</a></li>
                		   </ul> </li>
<?php if (!is_user()): ?>
<li><a href="/register" ><span class="l"></span><span class="r"></span><span class="t">Регистрация</span></a></li>
 <?php else: ?>
  <li><a href="/logout" onclick="return confirm(\'Вы действительно хотите выйти?\')"><span class="l"></span><span class="r"></span><span class="t">Выход</span></a></li>
<?php endif; ?>

</ul></div>
                <div class="art-contentLayout">
                    <div class="art-sidebar1">
                        <div class="art-Block">
                            <div class="art-Block-tl"></div>
                            <div class="art-Block-tr"></div>
                            <div class="art-Block-bl"></div>
                            <div class="art-Block-br"></div>
                            <div class="art-Block-tc"></div>
                            <div class="art-Block-bc"></div>
                            <div class="art-Block-cl"></div>
                            <div class="art-Block-cr"></div>
                            <div class="art-Block-cc"></div>
                            <div class="art-Block-body">
                                <div class="art-BlockContent">
                                    <div class="art-BlockContent-body">
                                        <div>


<?php if (is_user()): ?>

<?php if (is_admin()): ?>
    <div class="nmenu">
    <i class="fa fa-wrench"></i> <a href="/admin">Панель</a>

    <?php if (stats_spam()>0): ?>
        &bull; <a href="/admin/spam"><span style="color:#ff0000">Спам!</span></a>
    <?php endif; ?>

    <?php if (user('newchat')<stats_newchat()): ?>
        &bull; <a href="/admin/chat"><span style="color:#ff0000">Чат</span></a>
    <?php endif; ?>

    </div>
<?php endif; ?>

  <?php include (APP.'/views/main/menu.blade.php'); ?>

<?php else: ?>

<?php $cooklog = (isset($_COOKIE['login'])) ? check($_COOKIE['login']): ''; ?>

<div class="divb">Авторизация</div>

<form method="post" action="/login<?= returnUrl() ?>">
Логин:<br><input name="login" value="'.$cooklog.'"><br>
Пароль:<br><input name="pass" type="password"><br>
Запомнить меня:
<input name="cookietrue" type="checkbox" value="1" checked="checked"><br>

<input value="Войти" type="submit"></form>

<a href="/register">Регистрация</a><br>
<a href="/recovery">Забыли пароль?</a>
<?php endif; ?>


</div>
                                    </div>
                                </div>
                            </div>
                        </div>



<div class="art-Block">
                            <div class="art-Block-tl"></div>
                            <div class="art-Block-tr"></div>
                            <div class="art-Block-bl"></div>
                            <div class="art-Block-br"></div>
                            <div class="art-Block-tc"></div>
                            <div class="art-Block-bc"></div>
                            <div class="art-Block-cl"></div>
                            <div class="art-Block-cr"></div>
                            <div class="art-Block-cc"></div>
                            <div class="art-Block-body">
                                <div class="art-BlockContent">
                                    <div class="art-BlockContent-body">
                                        <div>
<div class="divb">Календарь</div>
<?php include (APP.'/Includes/calendar.php'); ?>
</div>
                                    </div>
                                </div>
                            </div>
                        </div>



<div class="art-Block">
                            <div class="art-Block-body">
<div class="art-BlockContent">


                                </div>
                            </div>
                        </div>
                    </div>

<div class="art-content">
                        <div class="art-Post">
                            <div class="art-Post-tl"></div>
                            <div class="art-Post-tr"></div>
                            <div class="art-Post-bl"></div>
                            <div class="art-Post-br"></div>
                            <div class="art-Post-tc"></div>
                            <div class="art-Post-bc"></div>
                            <div class="art-Post-cl"></div>
                            <div class="art-Post-cr"></div>
                            <div class="art-Post-cc"></div>
                            <div class="art-Post-body">
                        <div class="art-Post-inner">

<div class="art-PostMetadataHeader">
<?= view('includes/note'); ?>
</div>


                                <h2 class="art-PostHeaderIcon-wrapper">
                                    <img src="/themes/sky/img/PostHeaderIcon.png" width="29" height="29" alt="PostHeaderIcon">
                                    <span class="art-PostHeader"><?= setting('title') ?></span>
                                </h2>

                            <div class="art-PostContent">



<div>
