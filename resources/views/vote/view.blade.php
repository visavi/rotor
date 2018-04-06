@extends('layout')

@section('title')
    {{ $vote->title }}
@stop

@section('content')

    <h1>{{ $vote->title }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/votes">Голосования</a></li>
            <li class="breadcrumb-item active">{{ $vote->title }}</li>
        </ol>
    </nav>

    @if ($vote->topic->id)
        Тема: <a href="/topic/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a><br><br>
    @endif

    @if ((getUser() && empty($vote->poll)) && empty($show))
        <form action="/votes/{{ $vote->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach($vote->answers as $answer)
                <label><input name="poll" type="radio" value="{{ $answer['id'] }}"> {{ $answer['answer'] }}</label><br>
            @endforeach
            <br>
            <button class="btn btn-sm btn-primary">Голосовать</button>
        </form><br>

        Проголосовало: <b>{{ $vote->count }}</b><br><br>
        <i class="fa fa-history"></i> <a href="/votes/{{ $vote->id }}?show=true">Результаты</a><br>

    @else
        @foreach ($vote->voted as $key => $data)
            <?php $proc = round(($data * 100) / $vote->sum, 1); ?>
            <?php $maxproc = round(($data * 100) / $vote->max); ?>

            <b>{{ $key }}</b> (Голосов: {{ $data }})<br>
            {!! progressBar($maxproc, $proc.'%') !!}
        @endforeach

        Проголосовало: <b>{{ $vote->count }}</b><br><br>

        @if (! empty($show))
            <i class="fa fa-chart-bar"></i> <a href="/votes/{{ $vote->id }}">К вариантам</a><br>
        @endif
        <i class="fa fa-users"></i> <a href="/votes/voters/{{ $vote->id }}">Проголосовавшие</a><br>
    @endif
@stop
