@extends('layout')

@section('title')
    Поиск по сайту - @parent
@stop

@section('content')

    <h1>Поиск по сайту</h1>

    Откройте в редакторе файлов страницу:<br>
    <code>search/index</code><br><br>
    или через файловую систему:<br>
    <code>app/views/search/index.blade.php</code><br><br>
    Вставьте сюда сформированный код<br><br>

    @if (is_admin([101]))
        <a href="/admin/files?act=edit&amp;path=search/&amp;file=index">Ссылка на редактирование</a><br><br>
    @endif

    Сссылка на документацию по созданию поиска:<br>
    <a href="https://site.yandex.ru">Yandex - поиск для сайта</a><br>
    <a href="https://cse.google.ru">Google - Пользовательский поиск</a><br>

@stop
