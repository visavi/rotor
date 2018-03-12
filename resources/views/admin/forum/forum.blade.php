@extends('layout')

@section('title')
    {{ $forum->title }} (Стр. {{ $page['current'] }})
@stop

@section('content')

    <div class="float-right">
        <a class="btn btn-success" href="/forum/create?fid={{ $forum->id }}">Создать тему</a>
    </div>

    <h1>{{ $forum->title }}</h1>

    <a href="/admin/forum">Форум</a>

    @if ($forum->parent->id)
        / <a href="/admin/forum/{{ $forum->parent->id }}">{{ $forum->parent->title }}</a>
    @endif

    / {{ $forum->title }}
    / <a href="/forum/{{ $forum->id  }}?page={{ $page['current'] }}">Обзор</a>
    <hr>

    @if ($topics->isNotEmpty())
            @foreach ($topics as $topic)
                <div class="b" id="topic_{{ $topic->id }}">

                    <div class="float-right">
                        <a href="/admin/topic/edit/{{ $topic->id }}" title="Редактировать"><i class="fa fa-pencil-alt text-muted"></i></a>
                        <a href="/admin/topic/move/{{ $topic->id }}" title="Перенести"><i class="fa fa-arrows-alt text-muted"></i></a>
                        <a href="/admin/topic/delete/{{ $topic->id }}?page={{ $page['current'] }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данную тему?')" title="Удалить"><i class="fa fa-times text-muted"></i></a>
                    </div>

                    <i class="fa {{ $topic->getIcon() }} text-muted"></i>
                    <b><a href="/admin/topic/{{ $topic->id }}">{{ $topic->title }}</a></b> ({{ $topic->count_posts }})
                </div>
                <div>
                    @if ($topic->lastPost)
                        {!! $topic->pagination('/admin/topic') !!}
                        Сообщение: {{ $topic->lastPost->user->login }} ({{ dateFixed($topic->lastPost->created_at) }})
                    @endif
                </div>
            @endforeach
        {!! pagination($page) !!}

    @elseif ($forum->closed)
        {!! showError('В данном разделе запрещено создавать темы!') !!}
    @else
        {!! showError('Тем еще нет, будь первым!') !!}
    @endif
@stop
