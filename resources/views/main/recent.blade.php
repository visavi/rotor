@extends('layout')

@section('title')
    Последняя активность - @parent
@stop

@section('content')
    <h1>Последняя активность</h1>

    <div class="b"><i class="fa fa-forumbee fa-lg text-muted"></i> <b>Последние темы</b></div>
    {{ recentTopics() }}

    <div class="b"><i class="fa fa-download fa-lg text-muted"></i> <b>Последние файлы</b></div>
    {{ recentFiles() }}

    <div class="b"><i class="fa fa-globe fa-lg text-muted"></i> <b>Последние статьи</b></div>
    {{ recentBlogs() }}

    <div class="b"><i class="fa fa-image fa-lg text-muted"></i> <b>Последние фотографии</b></div>
    {{  recentPhotos() }}
@stop
