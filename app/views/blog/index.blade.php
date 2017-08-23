@extends('layout')

@section('title')
    Блоги - Список разделов - @parent
@stop

@section('content')

    <h1>Блоги</h1>

    @if (is_user())
        Мои: <a href="/blog/active/blogs">статьи</a>, <a href="/blog/active/comments">комментарии</a> /
    @endif

    Новые: <a href="/blog/new/blogs">статьи</a>, <a href="/blog/new/comments">комментарии</a><hr>

    @foreach($blogs as $key => $data)
        <i class="fa fa-folder-open"></i> <b><a href="/blog/{{ $data['id'] }}">{{ $data['name'] }}</a></b>

        @if ($data->new)
            ({{ $data->count }}/+{{ $data->new->count }})<br>
        @else
            ({{ $data->count }})<br>
        @endif
    @endforeach

    <br>
    <a href="/blog/top">Топ статей</a> /
    <a href="/blog/tags">Облако тегов</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blogs">Все статьи</a> /
    <a href="/blog/rss">RSS</a><br>
@stop
