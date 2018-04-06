@extends('layout')

@section('title')
    Форум
@stop

@section('content')

    <h1>Форум {{ setting('title') }}</h1>

    @include('advert/_forum')

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Форум</li>
        </ol>
    </nav>

    @if (getUser())
        Мои: <a href="/forum/active/themes">темы</a>, <a href="/forum/active/posts">сообщения</a>, <a href="/forum/bookmark">закладки</a> /
    @endif

    Новые: <a href="/forum/new/themes">темы</a>, <a href="/forum/new/posts">сообщения</a>
    <hr/>

    @if ($forums->isNotEmpty())
        @foreach ($forums as $forum)
            <div class="b">
                <i class="fa fa-file-alt fa-lg text-muted"></i>
                <b><a href="/forum/{{ $forum->id }}">{{ $forum->title }}</a></b>
                ({{ $forum->count_topics }}/{{ $forum->count_posts }})

                @if ($forum->description)
                    <p><small>{{ $forum->description }}</small></p>
                @endif
            </div>

            <div>
                @if ($forum->children->isNotEmpty())
                    @foreach ($forum->children as $child)
                        <i class="fa fa-copy text-muted"></i> <b><a href="/forum/{{ $child->id }}">{{ $child->title }}</a></b>
                        ({{ $child->count_topics }}/{{ $child->count_posts }})<br/>
                    @endforeach
                @endif

                @if ($forum->lastTopic->lastPost->id)
                    Тема: <a href="/topic/end/{{ $forum->lastTopic->id }}">{{ $forum->lastTopic->title }}</a>
                    <br/>
                    Сообщение: {{ $forum->lastTopic->lastPost->user->login }} ({{ dateFixed($forum->lastTopic->lastPost->created_at) }})
                @else
                    Темы еще не созданы!
                @endif
            </div>
        @endforeach
    @else
        {!! showError('Разделы форума еще не созданы!') !!}
    @endif

    <br/><a href="/rules">Правила</a> / <a href="/forum/top/themes">Топ тем</a> / <a href="/forum/top/posts">Топ постов</a> / <a href="/forum/search">Поиск</a> / <a href="/forum/rss">RSS</a><br/>
@stop
