@extends('layout')

@section('title')
    Главная страница
@stop

@section('content')

    @include('advert/_top')

    <h1>{{ setting('title') }}</h1>

    <i class="far fa-circle fa-lg text-muted"></i> <a href="/news">Новости сайта</a> ({{ statsNewsDate() }})<br> {{ lastNews() }}

    <div class="b">
        <i class="fa fa-comment fa-lg text-muted"></i> <b><a href="/pages/recent">Общение</a></b>
    </div>
    <i class="far fa-circle fa-lg text-muted"></i> <a class="index" href="/guestbooks">Гостевая книга</a> ({{  statsGuestbook() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/photos">Фотогалерея</a> ({{ statsPhotos() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/votes">Голосования</a> ({{ statVotes()}})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/offers">Предложения / Проблемы</a> ({{ statsOffers() }})<br>

    <div class="b">
        <i class="fab fa-forumbee fa-lg text-muted"></i>
        <b><a href="/forums">Форум</a></b> ({{  statsForum() }})
    </div>
    {{ recentTopics() }}

    <div class="b">
        <i class="fa fa-download fa-lg text-muted"></i> <b><a href="/loads">Загрузки</a></b> ({{ statsLoad() }})
    </div>
    {{ recentFiles() }}

    <div class="b">
        <i class="fa fa-globe fa-lg text-muted"></i>
        <b><a href="/blogs">Блоги</a></b> ({{ statsBlog() }})
    </div>
    {{ recentBlogs() }}

    <div class="b">
        <i class="fa fa-cog fa-lg text-muted"></i>
        <b><a href="/pages">Сервисы сайта</a></b>
    </div>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/files/docs">Документация RotorCMS</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/search">Поиск по сайту</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/mails">Обратная связь</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/users">Список юзеров</a> ({{  statsUsers() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/administrators">Администрация</a> ({{ statsAdmins() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/pages/stat">Информация</a><br>

    <div class="b">
        <i class="fa fa-chart-line fa-lg text-muted"></i> <b>Курсы валют</b>
    </div>
    {!! getCourses() !!}

    <div class="b">
        <i class="fa fa-calendar-alt fa-lg text-muted"></i> <b>Календарь</b>
    </div>
    {!! getCalendar() !!}

    @include('advert/_bottom')
@stop
