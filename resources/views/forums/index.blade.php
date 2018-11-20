@extends('layout')

@section('title')
    Форум
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Форум</li>
        </ol>
    </nav>

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/forums/create">Создать тему</a>
        </div><br>
    @endif

    <h1>Форум {{ setting('title') }}</h1>

    @include('advert/_forum')

    @if (getUser())
        Мои: <a href="/forums/active/topics">темы</a>, <a href="/forums/active/posts">сообщения</a>, <a href="/forums/bookmarks">закладки</a> /
    @endif

    Новые: <a href="/topics">темы</a>, <a href="/posts">сообщения</a>
    <hr/>

    @if ($forums->isNotEmpty())
        @foreach ($forums as $forum)
            <div class="b">
                <i class="fa fa-file-alt fa-lg text-muted"></i>
                <b><a href="/forums/{{ $forum->id }}">{{ $forum->title }}</a></b>
                ({{ $forum->count_topics }}/{{ $forum->count_posts }})

                @if ($forum->description)
                    <p><small>{{ $forum->description }}</small></p>
                @endif
            </div>

            <div>
                @if ($forum->children->isNotEmpty())
                    @foreach ($forum->children as $child)
                        <i class="fa fa-copy text-muted"></i> <b><a href="/forums/{{ $child->id }}">{{ $child->title }}</a></b>
                        ({{ $child->count_topics }}/{{ $child->count_posts }})<br/>
                    @endforeach
                @endif

                @if ($forum->lastTopic->lastPost->id)
                    Тема: <a href="/topics/end/{{ $forum->lastTopic->id }}">{{ $forum->lastTopic->title }}</a>
                    <br/>
                    Сообщение: {!! $forum->lastTopic->lastPost->user->getProfile(null, false) !!} ({{ dateFixed($forum->lastTopic->lastPost->created_at) }})
                @else
                    Темы еще не созданы!
                @endif
            </div>
        @endforeach
    @else
        {!! showError('Разделы форума еще не созданы!') !!}
    @endif

    <br/><a href="/rules">Правила</a> / <a href="/forums/top/topics">Топ тем</a> / <a href="/forums/top/posts">Топ постов</a> / <a href="/forums/search">Поиск</a> / <a href="/forums/rss">RSS</a><br/>
@stop
