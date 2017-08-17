<?php
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>
        @section('title')
            {{ Setting::get('title') }}
        @show
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="image_src" href="/assets/img/images/icon.png">
    <?= include_style() ?>
    <link rel="stylesheet" href="/themes/bluewater/css/style.css">
    <link rel="alternate" href="/news/rsss" title="RSS News" type="application/rss+xml">
    <?= include_javascript() ?>
    <meta name="keywords" content="%KEYWORDS%">
    <meta name="description" content="%DESCRIPTION%">
    <meta name="generator" content="RotorCMS <?= env('VERSION') ?>">
</head>
<body>
<!--Design by WmLiM (http://komwap.ru)-->

<div id="wrap">
    <div id="header">
        <h1 id="logo-text"><a href="/"><?= Setting::get('title') ?></a></h1>
        <p id="slogan"><?= Setting::get('logos') ?></p>

        <div id="header-links">
            <p>

<?php
if (is_user()){

    echo user_gender(App::user()).profile(App::user());
    if (is_admin()){

        echo ' | <a href="/admin">Админ-панель</a>';
        if (stats_spam()>0){
        echo ' | <a href="/admin/spam"><span style="color:#ff0000">Спам!</span></a>';
        }

        if (App::user('newchat')<stats_newchat()){
        echo ' | <a href="/admin/chat"><span style="color:#ff0000">Чат</span></a>';
        }

    }

} else {
    echo '<a href="/login'.App::returnUrl().'">Авторизация</a> | ';
    echo '<a href="/register">Регистрация</a>';
}
?>
            </p>
        </div>
    </div>

    <!-- navigation -->
    <div id="menu">
        <ul>
            <li><a href="/">Главная</a></li>
            <li><a href="/forum">Форум</a></li>
            <li><a href="/load">Загрузки</a></li>
            <li><a href="/blog">Блоги</a></li>
            <li><a href="/gallery">Галерея</a></li>
        </ul>
    </div>

    <!-- content-wrap starts here -->
    <div id="content-wrap">
        <div id="sidebar">

            <?php
            if (is_user()) {
                include (APP.'/views/main/menu.blade.php');
            } else {
                include (APP.'/views/main/recent.blade.php');
            }
            ?>
        </div>
        <div id="main">
            <div class="body_center">
<?= App::view('includes/note'); /*Временно пока шаблоны подключаются напрямую*/ ?>
