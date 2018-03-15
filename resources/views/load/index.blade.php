@extends('layout')

@section('title')
    Загрузки
@stop

@section('content')

    <h1>Загрузки</h1>

    @if (getUser())
        Мои: <a href="/load/active?act=files">файлы</a>, <a href="/load/active?act=comments">комментарии</a> /
    @endif

    Новые: <a href="/down/new/files">файлы</a>, <a href="/down/new/comments">комментарии</a>
    <hr>

    <div class="b">
        <i class="fa fa-folder-open"></i>
        <b><a href="/load/fresh">Свежие загрузки</a></b> ({{ $new }})<br>
    </div>

    @foreach ($cats as $category)
        <div class="b">
            <i class="fa fa-folder-open"></i>
            <b><a href="/load/{{ $category->id }}">{{ $category->name }}</a></b>
            @if ($category->new)
                ({{ $category->count_downs }}/<span style="color:#ff0000">+{{ $category->new->count_downs }}</span>)<br>
            @else
                ({{ $category->count_downs }})<br>
            @endif
        </div>

        <div>
            @if ($category->children->isNotEmpty())
                @foreach ($category->children as $child)
                    <i class="fa fa-angle-right"></i> <b><a href="/load/{{ $child->id }}">{{ $child['name'] }}</a></b>
                    @if ($child->new)
                        ({{ $child->count_downs }}/<span style="color:#ff0000">+{{ $child->new->count_downs }}</span>)<br>
                    @else
                        ({{ $child->count_downs }})<br>
                    @endif
                @endforeach
            @endif
        </div>
    @endforeach

    <br>
    <a href="/load/top">Топ файлов</a> /
    <a href="/load/search">Поиск</a> /
    <a href="/load/add">Добавить файл</a> /
    <a href="/down/rss">RSS</a><br>
@stop
