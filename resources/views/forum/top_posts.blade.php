@extends('layout')

@section('title')
    Топ популярных постов
@stop

@section('content')
    <h1>Топ популярных постов</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forum">Форум</a></li>
            <li class="breadcrumb-item active">Топ популярных постов</li>
        </ol>
    </nav>

    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)
            <div class="b">
                <i class="fa fa-file-alt"></i> <b><a href="/topic/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>
                (Рейтинг: {{ $data->rating }})
            </div>
            <div>
                {!! bbCode($data->text) !!}<br>

                Написал: {{ $data->user->login }} {!! userOnline($data->user) !!} <small>({{ dateFixed($data->created_at) }})</small><br>

                <?php if (isAdmin()): ?>
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                <?php endif; ?>

            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Сообщений еще нет!') !!}
    @endif
@stop
