@extends('layout')

@section('title')
    Загрузки
@stop

@section('content')

    <h1>Загрузки</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Загрузки</li>
        </ol>
    </nav>

    @if (getUser())
        Мои: <a href="/downs/active/files">файлы</a>, <a href="/downs/active/comments">комментарии</a> /
    @endif

    Новые: <a href="/downs/new/files">файлы</a>, <a href="/downs/new/comments">комментарии</a>
    <hr>

    @foreach ($categories as $category)
        <div class="b">
            <i class="fa fa-folder-open"></i>
            <b><a href="/loads/{{ $category->id }}">{{ $category->name }}</a></b>
            @if ($category->new)
                ({{ $category->count_downs }}/<span style="color:#ff0000">+{{ $category->new->count_downs }}</span>)<br>
            @else
                ({{ $category->count_downs }})<br>
            @endif
        </div>

        <div>
            @if ($category->children->isNotEmpty())
                @foreach ($category->children as $child)
                    <i class="fa fa-angle-right"></i> <b><a href="/loads/{{ $child->id }}">{{ $child['name'] }}</a></b>
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
    <a href="/loads/top">Топ файлов</a> /
    <a href="/loads/search">Поиск</a> /
    <a href="/loads/rss">RSS</a><br>
@stop
