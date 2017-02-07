@extends('layout')

@section('title')
    Форум - @parent
@stop

@section('content')

    <h1>Форум {{ App::setting('title') }}</h1>
    @if (is_user())
        Мои: <a href="/forum/active/themes">темы</a>, <a href="/forum/active/posts">сообщения</a>, <a href="/forum/bookmark">закладки</a> /
    @endif

    Новые: <a href="/forum/new/themes">темы</a>, <a href="/forum/new/posts">сообщения</a>
    <hr/>

    @foreach($forums as $forum)
        <div class="b">
            <i class="fa fa-file-text-o fa-lg text-muted"></i>
            <b><a href="/forum/{{ $forum['id'] }}">{{ $forum['title'] }}</a></b>
            ({{ $forum->countTopic->count }}/{{ $forum->countPost->count }})

            @if (!empty($forum['desc']))
                <br/>
                <small>{{ $forum['desc'] }}</small>
            @endif
        </div>

        <div>
            @if ($forum->children)
                @foreach($forum->children as $child)
                    <i class="fa fa-files-o text-muted"></i> <b><a href="/forum/{{ $child['id'] }}">{{ $child['title'] }}</a></b>
                    ({{ $child->countTopic->count }}/{{ $child->countPost->count }})<br/>
                @endforeach
            @endif

            @if ($forum->lastTopic->lastPost)
                Тема: <a href="/topic/{{ $forum->lastTopic->id }}/end">{{ $forum->lastTopic->title }}</a>
                <br/>
                Сообщение: {{ $forum->lastTopic->lastPost->user->login }} ({{ date_fixed($forum->lastTopic->lastPost->time) }})
            @else
                Темы еще не созданы!
            @endif
        </div>
    @endforeach

    <br/><a href="/rules">Правила</a> / <a href="/forum/top/themes">Топ тем</a> / <a href="/forum/search">Поиск</a> / <a href="/forum/rss">RSS</a><br/>
@stop
