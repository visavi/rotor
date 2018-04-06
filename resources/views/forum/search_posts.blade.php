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

    <p>Найдено совпадений в сообщениях: {{ $page->total }}</p>

    @foreach ($posts as $post)

        <div class="b">
            <i class="fa fa-file-alt"></i> <b><a href="/topic/{{ $post->topic_id }}/{{ $post->id }}">{{ $post->topic->title }}</a></b>
        </div>

        <div>{!! bbCode($post->text) !!}<br>
            Раздел: <a href="/topic/{{ $post->topic->forum->id }}">{{ $post->topic->forum->title }}</a><br>
            Написал: {!! profile($post->user) !!} {!! userOnline($post->user) !!} <small>({{ dateFixed($post->created_at) }})</small><br>
        </div>

    @endforeach

    {!! pagination($page) !!}
@stop
