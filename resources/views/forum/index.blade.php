@extends('layout')

@section('title')
    Форум - @parent
@stop

@section('content')

    <h1>Форум {{ setting('title') }}</h1>

    @include('advert/_forum')

    @if (isUser())
        Мои: <a href="/forum/active/themes">темы</a>, <a href="/forum/active/posts">сообщения</a>, <a href="/forum/bookmark">закладки</a> /
    @endif

    Новые: <a href="/forum/new/themes">темы</a>, <a href="/forum/new/posts">сообщения</a>
    <hr/>

    @foreach ($forums as $forum)
        <div class="b">
            <i class="fa fa-file-text-o fa-lg text-muted"></i>
            <b><a href="/forum/{{ $forum['id'] }}">{{ $forum['title'] }}</a></b>
            ({{ $forum->topics }}/{{ $forum->posts }})

            @if (!empty($forum['desc']))
                <br/>
                <small>{{ $forum['desc'] }}</small>
            @endif
        </div>

        <div>
            @if ($forum->children->isNotEmpty())
                @foreach ($forum->children as $child)
                    <i class="fa fa-files-o text-muted"></i> <b><a href="/forum/{{ $child['id'] }}">{{ $child['title'] }}</a></b>
                    ({{ $child->topics }}/{{ $child->posts }})<br/>
                @endforeach
            @endif

            @if ($forum->lastTopic->lastPost->id)
                Тема: <a href="/topic/{{ $forum->lastTopic->id }}/end">{{ $forum->lastTopic->title }}</a>
                <br/>
                Сообщение: {{ $forum->lastTopic->lastPost->user->login }} ({{ dateFixed($forum->lastTopic->lastPost->created_at) }})
            @else
                Темы еще не созданы!
            @endif
        </div>
    @endforeach

    <br/><a href="/rules">Правила</a> / <a href="/forum/top/themes">Топ тем</a> / <a href="/forum/top/posts">Топ постов</a> / <a href="/forum/search">Поиск</a> / <a href="/forum/rss">RSS</a><br/>
@stop
