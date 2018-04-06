@extends('layout')

@section('title')
    Форум - Список сообщений {{ $user->login }} (Стр. {{ $page['current'] }})
@stop

@section('content')

    <h1>Список сообщений {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forum">Форум</a></li>
            <li class="breadcrumb-item active">Список сообщений {{ $user->login }}</li>
        </ol>
    </nav>

    @foreach ($posts as $data)
        <div class="post">
            <div class="b">
                <i class="fa fa-file-alt"></i> <b><a href="/topic/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>

                @if (isAdmin())
                    <a href="#" class="float-right" onclick="return deletePost(this)" data-tid="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-times"></i></a>
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
