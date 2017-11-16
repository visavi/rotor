@extends('layout')

@section('title')
    Список сообщений {{ $user->login }}
@stop

@section('content')

    <h1>Список сообщений {{ $user->login }}</h1>

    <a href="/forum">Форум</a>

    @foreach ($posts as $data)
        <div class="post">
            <div class="b">
                <i class="fa fa-file-text-o"></i> <b><a href="/topic/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>

                @if (isAdmin())
                    <a href="#" class="float-right" onclick="return deletePost(this)" data-tid="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove"></i></a>
                @endif
            </div>
            <div>
                {!! bbCode($data->text) !!}<br>

                Написал: {{ $data->user->login }}
                <small>({{ dateFixed($data->created_at) }})</small>
                <br>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif
            </div>
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
