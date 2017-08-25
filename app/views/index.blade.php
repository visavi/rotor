@extends('layout')

@section('title')
    Главная страница - @parent
@stop

@section('content')

    @include('advert.top')

    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/news">Новости сайта</a> (<?=stats_news()?>)<br> <?=last_news()?>

    <div class="b">
        <i class="fa fa-comment fa-lg text-muted"></i> <b><a href="/page/recent">Общение</a></b>
    </div>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a class="index" href="/book">Гостевая книга</a> (<?=stats_guest()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/gallery">Фотогалерея</a> (<?=stats_gallery()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/votes">Голосования</a> (<?=stats_votes()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/offers">Предложения / Проблемы</a> (<?=stats_offers()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/games">Игры и развлечения</a><br>

    <div class="b">
        <i class="fa fa-forumbee fa-lg text-muted"></i>
        <b><a href="/forum">Форум</a></b> (<?=stats_forum()?>)
    </div>
    <?=recenttopics()?>

    <div class="b">
        <i class="fa fa-download fa-lg text-muted"></i> <b><a href="/load">Загрузки</a></b> (<?=stats_load()?>)
    </div>
    <?=recentfiles()?>

    <div class="b">
        <i class="fa fa-globe fa-lg text-muted"></i>
        <b><a href="/blog">Блоги</a></b> (<?=stats_blog()?>)
    </div>
    <?=recentblogs()?>

    <div class="b">
        <i class="fa fa-cog fa-lg text-muted"></i>
        <b><a href="/page">Сервисы сайта</a></b>
    </div>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/files/docs">Документация RotorCMS</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/search">Поиск по сайту</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/mail">Обратная связь</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/userlist">Список юзеров</a> (<?=stats_users()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/adminlist">Администрация</a> (<?=stats_admins()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/page/stat">Информация</a><br>

    <div class="b">
        <i class="fa fa-comment fa-lg text-muted"></i> <b>Курсы валют</b>
    </div>
    <?php include_once(APP.'/includes/courses.php') ?>

    <div class="b">
        <i class="fa fa-calendar fa-lg text-muted"></i> <b>Календарь</b>
    </div>
    <?php include_once(APP.'/includes/calendar.php') ?>

    @include('advert.bottom')
@stop
