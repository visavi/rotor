@extends('layout')

@section('title')
    Поиск запроса {{ $find }}
@stop

@section('content')

    <h1>Поиск запроса {{ $find }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forum">Форум</a></li>
            <li class="breadcrumb-item"><a href="/forum/search">Поиск</a></li>
            <li class="breadcrumb-item active">Поиск запроса</li>
        </ol>
    </nav>

    <p>Найдено совпадений в темах: {{ $page->total }}</p>

    @foreach ($topics as $topic)
        <div class="b">
            <i class="fa {{ $topic->getIcon() }} text-muted"></i>
            <b><a href="/topic/{{ $topic->id }}">{{ $topic->title }}</a></b> ({{ $topic->count_posts }})
        </div>
        <div>
            {!! $topic->pagination() !!}
            Раздел: <a href="/topic/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a><br>
            Сообщение: {{ $topic->lastPost->user->login }} ({{ dateFixed($topic->lastPost->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
