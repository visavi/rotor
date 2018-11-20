@extends('layout')

@section('title')
    Форум - Новые сообщения (Стр. {{ $page->current }})
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">Форум</a></li>
            <li class="breadcrumb-item active">Новые сообщения</li>
        </ol>
    </nav>

    <h1>Новые сообщения</h1>

    @foreach ($posts as $data)
        <div class="b">
            <i class="fa fa-file-alt"></i> <b><a href="/topics/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>
            ({{ $data->topic->count_posts }})
        </div>
        <div>
            {!! bbCode($data->text) !!}<br>

            Написал: {{ $data->user->login }} <small>({{ dateFixed($data->created_at) }})</small><br>

            @if (isAdmin())
                <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
            @endif

        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
