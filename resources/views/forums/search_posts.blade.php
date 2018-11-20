@extends('layout')

@section('title')
    Поиск запроса {{ $find }}
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">Форум</a></li>
            <li class="breadcrumb-item"><a href="/forums/search">Поиск</a></li>
            <li class="breadcrumb-item active">Поиск запроса</li>
        </ol>
    </nav>

    <h1>Поиск запроса {{ $find }}</h1>

    <p>Найдено совпадений в сообщениях: {{ $page->total }}</p>

    @foreach ($posts as $post)

        <div class="b">
            <i class="fa fa-file-alt"></i> <b><a href="/topics/{{ $post->topic_id }}/{{ $post->id }}">{{ $post->topic->title }}</a></b>
        </div>

        <div>{!! bbCode($post->text) !!}<br>
            Раздел: <a href="/topics/{{ $post->topic->forum->id }}">{{ $post->topic->forum->title }}</a><br>
            Написал: {!! $post->user->getProfile() !!} <small>({{ dateFixed($post->created_at) }})</small><br>
        </div>

    @endforeach

    {!! pagination($page) !!}
@stop
