@extends('layout')

@section('title')
    Блоги - Список разделов
@stop

@section('content')

    <h1>Блоги</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Блоги</li>
        </ol>
    </nav>

    @if (getUser())
        Мои: <a href="/blog/active/articles">статьи</a>, <a href="/blog/active/comments">комментарии</a> /
    @endif

    Новые: <a href="/blog/new/articles">статьи</a>, <a href="/blog/new/comments">комментарии</a><hr>

    @foreach ($categories as $key => $data)

        <div class="b">
            <i class="fa fa-folder-open"></i> <b><a href="/blog/{{ $data->id }}">{{ $data->name }}</a></b>

            @if ($data->new)
                ({{ $data->count_blogs }}/<span style="color:#ff0000">+{{ $data->new->count_blogs }}</span>)
            @else
                ({{ $data->count_blogs }})
            @endif
        </div>

        <div>
            @if ($data->children->isNotEmpty())
                @foreach ($data->children as $child)
                    <i class="fa fa-angle-right"></i> <b><a href="/blog/{{ $child->id }}">{{ $child->name }}</a></b>
                    @if ($child->new)
                        ({{ $child->count_blogs }}/<span style="color:#ff0000">+{{ $child->new->count_blogs }}</span>)
                    @else
                        ({{ $child->count_blogs }})
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
