@extends('layout')

@section('title')
    Список новых сообщений
@stop

@section('content')
    <h1>Список новых сообщений</h1>

    <a href="/forum">Форум</a>

    @foreach ($posts as $data)
        <div class="b">
            <i class="fa fa-file-text-o"></i> <b><a href="/topic/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>
            ({{ $data->topic->posts }})
        </div>
        <div>
            {!! bbCode($data->text) !!}<br>

            Написал: {{ $data->user->login }} {!! userOnline($data->user) !!} <small>({{ dateFixed($data->created_at) }})</small><br>

            @if (isAdmin())
                <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
            @endif

        </div>
    @endforeach

    {{ pagination($page) }}
@stop
