@extends('layout')

@section('title')
    Поиск по сайту
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Поиск по сайту</li>
        </ol>
    </nav>
@stop

@section('content')
    Откройте в редакторе файлов страницу:<br>
    <code>search/index</code><br><br>
    или через файловую систему:<br>
    <code>/resources/views/search/index.blade.php</code><br><br>
    Вставьте сюда сформированный код<br><br>

    Сссылка на документацию по созданию поиска:<br>
    <a href="https://site.yandex.ru">Yandex - поиск для сайта</a><br>
    <a href="https://cse.google.ru">Google - пользовательский поиск</a><br>
@stop
