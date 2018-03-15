@extends('layout')

@section('title')
    Форум - Новые сообщения (Стр. {{ $page['current'] }})
@stop

@section('content')
    <h1>Новые сообщения</h1>

    <a href="/forum">Форум</a>

    @foreach ($posts as $data)
        <div class="b">
            <i class="fa fa-file-alt"></i> <b><a href="/topic/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>
            ({{ $data->topic->count_posts }})
        </div>
        <div>
            {!! bbCode($data->text) !!}<br>

            Написал: {{ $data->user->login }} {!! userOnline($data->user) !!} <small>({{ dateFixed($data->created_at) }})</small><br>

            @if (isAdmin())
                <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
            @endif

        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
