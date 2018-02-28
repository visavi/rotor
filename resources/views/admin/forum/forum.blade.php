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

    @if ($forum->parent)
        / <a href="/admin/forum/{{ $forum->parent->id }}">{{ $forum->parent->title }}</a>
    @endif

    / {{ $forum->title }}
    / <a href="/forum/{{ $forum->id  }}?page={{ $page['current'] }}">Обзор</a>
    <hr>

    @if ($topics)

        <form action="/admin/topic/delete?page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="form text-right">
                <label for="all">Отметить все</label>
                <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked">
            </div>

        @foreach ($topics as $topic)
            <div class="b" id="topic_{{ $topic->id }}">

                <div class="float-right">

                    <a href="/admin/topic/edit/{{ $topic->id }}" title="Редактировать"><i class="fa fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/topic/move/{{ $topic->id }}" title="Перенести"><i class="fa fa-arrows-alt text-muted"></i></a>
                    <input type="checkbox" name="del[]" value="{{ $topic->id }}">
                </div>

                <i class="fa {{ $topic->getIcon() }} text-muted"></i>
                <b><a href="/admin/topic/{{ $topic->id }}">{{ $topic->title }}</a></b> ({{ $topic->posts }})
            </div>
            <div>
                @if ($topic->lastPost)
                    {!! $topic->pagination('/admin/topic') !!}
                    Сообщение: {{ $topic->lastPost->user->login }} ({{ dateFixed($topic->lastPost->created_at) }})
                @endif
            </div>
        @endforeach

        <div class="float-right">
            <button class="btn btn-sm btn-danger">Удалить выбранное</button>
        </div>

        {!! pagination($page) !!}

    @elseif ($forums->closed)
        {!! showError('В данном разделе запрещено создавать темы!') !!}
    @else
        {!! showError('Тем еще нет, будь первым!') !!}
    @endif
@stop
