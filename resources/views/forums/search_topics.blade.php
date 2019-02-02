@extends('layout')

@section('title')
    Поиск запроса {{ $find }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">Форум</a></li>
            <li class="breadcrumb-item"><a href="/forums/search">Поиск</a></li>
            <li class="breadcrumb-item active">Поиск запроса</li>
        </ol>
    </nav>
@stop

@section('content')
    <p>Найдено совпадений в темах: {{ $page->total }}</p>

    @foreach ($topics as $topic)
        <div class="b">
            <i class="fa {{ $topic->getIcon() }} text-muted"></i>
            <b><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></b> ({{ $topic->count_posts }})
        </div>
        <div>
            {!! $topic->pagination() !!}
            Раздел: <a href="/topics/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a><br>
            Сообщение: {{ $topic->lastPost->user->getName() }} ({{ dateFixed($topic->lastPost->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
