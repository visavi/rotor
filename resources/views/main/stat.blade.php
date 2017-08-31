@extends('layout')

@section('title')
    Статистика сайта - @parent
@stop

@section('content')
    <h1>Статистика сайта</h1>

    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/ratinglist">Рейтинг толстосумов</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/authoritylist">Рейтинг репутации</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/statusfaq">Статусы юзеров</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/who">Кто-где</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/onlinewho">Кто онлайн</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/api">API интерфейс</a><br>
@stop
