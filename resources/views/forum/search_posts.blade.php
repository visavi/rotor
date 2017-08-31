@extends('layout')

@section('title')
    Поиск запроса {{ $find }} - @parent
@stop

@section('content')

    <h1>Поиск запроса {{ $find }}</h1>

    <p>Найдено совпадений в сообщениях: {{ $page['total'] }}</p>

    @foreach ($posts as $post)

        <div class="b">
            <i class="fa fa-file-text-o"></i> <b><a href="/topic/{{ $post['topic_id'] }}/{{ $post['id'] }}">{{ $post->getTopic()->title }}</a></b>
        </div>

        <div>{!! bbCode($post['text']) !!}<br>
            Написал: {!! profile($post->user) !!} {!! user_online($post->user) !!} <small>({{ date_fixed($post['created_at']) }})</small><br>
        </div>

    @endforeach

    {{ pagination($page) }}

    <i class="fa fa-arrow-circle-left"></i> <a href="/forum/search">Вернуться</a>
@stop
