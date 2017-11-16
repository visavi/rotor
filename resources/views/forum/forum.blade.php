@extends('layout')

@section('title')
    {{ $forum->title }} (Стр. {{ $page['current'] }})
@stop

@section('content')

    @if (getUser() && ! $forum->closed)
        <div class="float-right">
            <a class="btn btn-success" href="/forum/create?fid={{ $forum->id }}">Создать тему</a>
        </div>
    @endif

    <h1>{{ $forum->title }}</h1>

    <a href="/forum">Форум</a>

    @if ($forum->parent)
        / <a href="/forum/{{ $forum->parent->id }}">{{ $forum->parent->title }}</a>
    @endif

    / {{ $forum->title }}

    @if (isAdmin())
        / <a href="/admin/forum?act=forum&amp;fid={{  $forum->id  }}&amp;page={{ $page['current'] }}">Управление</a>
    @endif

    <hr>

    @if ($forum->children->isNotEmpty() && $page['current'] == 1)
        <div class="act">

        @foreach ($forum->children as $child)

            <div class="b"><i class="fa fa-file-text-o fa-lg text-muted"></i>
            <b><a href="/forum/{{ $child->id }}">{{ $child->title }}</a></b> ({{ $child->topics }}/{{ $child->posts }})</div>

            @if ($child->lastTopic)
                <div>
                    Тема: <a href="/topic/{{ $child->lastTopic->id }}/end">{{ $child->lastTopic->title }}</a><br>
                    @if ($child->lastTopic->lastPost)
                        Сообщение: {{ $child->lastTopic->lastPost->user->login }} ({{ dateFixed($child->lastTopic->lastPost->created_at) }})
                    @endif
                </div>
            @else
                <div>Темы еще не созданы!</div>
            @endif
        @endforeach

        </div>
        <hr>
    @endif

    @if ($topics)
        @foreach ($topics as $topic)
            <div class="b" id="topic_{{ $topic->id }}">
                <i class="fa {{ $topic->getIcon() }} text-muted"></i>
                <b><a href="/topic/{{ $topic->id }}">{{ $topic->title }}</a></b> ({{ $topic->posts }})
            </div>
            <div>
                @if ($topic->lastPost)
                    {!! $topic->pagination() !!}
                    Сообщение: {{ $topic->lastPost->user->login }} ({{ dateFixed($topic->lastPost->created_at) }})
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}

    @elseif ($forums->closed)
        {!! showError('В данном разделе запрещено создавать темы!') !!}
    @else
        {!! showError('Тем еще нет, будь первым!') !!}
    @endif


    <a href="/rules">Правила</a> /
    <a href="/forum/top/themes">Топ тем</a> /
    <a href="/forum/top/posts">Топ постов</a> /
    <a href="/forum/search?fid={{ $forum->id }}">Поиск</a><br>
@stop
