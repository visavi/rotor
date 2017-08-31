@extends('layout')

@section('title')
    Главная страница - @parent
@stop

@section('content')

    @include('advert/top')

    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/news">Новости сайта</a> (<?=statsNewsDate()?>)<br> <?=lastNews()?>

    <div class="b">
        <i class="fa fa-comment fa-lg text-muted"></i> <b><a href="/page/recent">Общение</a></b>
    </div>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a class="index" href="/book">Гостевая книга</a> (<?=statsGuest()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/gallery">Фотогалерея</a> (<?=statsGallery()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/votes">Голосования</a> (<?=statVotes()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/offers">Предложения / Проблемы</a> (<?php /*statsOffers()*/?>)<br>

    <div class="b">
        <i class="fa fa-forumbee fa-lg text-muted"></i>
        <b><a href="/forum">Форум</a></b> (<?=statsForum()?>)
    </div>
    <?=recentTopics()?>

    <div class="b">
        <i class="fa fa-download fa-lg text-muted"></i> <b><a href="/load">Загрузки</a></b> (<?php /*statsLoad()*/?>)
    </div>
    <?php /*recentFiles()*/?>

    <div class="b">
        <i class="fa fa-globe fa-lg text-muted"></i>
        <b><a href="/blog">Блоги</a></b> (<?=statsBlog()?>)
    </div>
    <?php /*recentBlogs()*/?>

    <div class="b">
        <i class="fa fa-cog fa-lg text-muted"></i>
        <b><a href="/page">Сервисы сайта</a></b>
    </div>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/files/docs">Документация RotorCMS</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/search">Поиск по сайту</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/mail">Обратная связь</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/userlist">Список юзеров</a> (<?php /*statsUsers()*/?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/adminlist">Администрация</a> (<?php /*statsAdmins()*/?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/page/stat">Информация</a><br>

    <div class="b">
        <i class="fa fa-comment fa-lg text-muted"></i> <b>Курсы валют</b>
    </div>
    <?php include_once(APP.'/Includes/courses.php') ?>

    <div class="b">
        <i class="fa fa-calendar fa-lg text-muted"></i> <b>Календарь</b>
    </div>
    <?php include_once(APP.'/Includes/calendar.php') ?>

    @include('advert/bottom')
@stop
