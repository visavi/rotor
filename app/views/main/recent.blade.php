@extends('layout')

@section('title')
    Последняя активность - @parent
@stop

@section('content')
    <h1>Последняя активность</h1>

    <div class="b"><i class="fa fa-forumbee fa-lg text-muted"></i> <b>Последние темы</b></div>
    {{ recenttopics() }}

    <div class="b"><i class="fa fa-download fa-lg text-muted"></i> <b>Последние файлы</b></div>
    {{ recentfiles() }}

    <div class="b"><i class="fa fa-globe fa-lg text-muted"></i> <b>Последние статьи</b></div>
    {{ recentblogs() }}

    <div class="b"><i class="fa fa-hashtag fa-lg text-muted"></i>  <b>Последние cобытия</b></div>
    {{ recentevents() }}

    <div class="b"><i class="fa fa-image fa-lg text-muted"></i> <b>Последние фотографии</b></div>
    {{  recentphotos() }}
@stop
