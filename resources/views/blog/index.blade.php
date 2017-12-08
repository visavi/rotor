@extends('layout')

@section('title')
    Блоги - Список разделов
@stop

@section('content')

    <h1>Блоги</h1>

    @if (getUser())
        Мои: <a href="/blog/active/articles">статьи</a>, <a href="/blog/active/comments">комментарии</a> /
    @endif

    Новые: <a href="/blog/new/articles">статьи</a>, <a href="/blog/new/comments">комментарии</a><hr>

    @foreach ($blogs as $key => $data)

        <div class="b">
            <i class="fa fa-folderpen"></i> <b><a href="/blog/{{ $data->id }}">{{ $data->name }}</a></b>

            @if ($data->new)
                ({{ $data->count }}/<span style="color:#ff0000">+{{ $data->new->count }}</span>)<br>
            @else
                ({{ $data->count }})<br>
            @endif
        </div>

        <div>
            @if ($data->children->isNotEmpty())
                @foreach ($data->children as $child)
                    <i class="fa fa-angle-right"></i> <b><a href="/blog/{{ $child->id }}">{{ $child['name'] }}</a></b>
                    @if ($child->new)
                        ({{ $child->count }}/<span style="color:#ff0000">+{{ $child->new->count }}</span>)<br>
                    @else
                        ({{ $child->count }})<br>
                    @endif
                @endforeach
            @endif
        </div>
    @endforeach

    <br>
    <a href="/blog/top">Топ статей</a> /
    <a href="/blog/tags">Облако тегов</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blogs">Все статьи</a> /
    <a href="/blog/rss">RSS</a><br>
@stop
