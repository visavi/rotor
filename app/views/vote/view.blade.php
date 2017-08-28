@extends('layout')

@section('title')
    {{ $vote->title }} - @parent
@stop

@section('content')

    <h1>{{ $vote->title }}</h1>

    @if ((is_user() && empty($vote['poll'])) && empty($show))
        <form action="/votes/{{ $vote->id }}/vote" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach($vote['answers'] as $answer)
                <label><input name="poll" type="radio" value="{{ $answer['id'] }}"> {{ $answer['answer'] }}</label><br>
            @endforeach
            <br>
            <button class="btn btn-sm btn-primary">Голосовать</button>
        </form><br>

        Проголосовало: <b>{{ $vote['count'] }}</b><br><br>
        <i class="fa fa-history"></i> <a href="/votes/{{ $vote->id }}?show=true">Результаты</a><br>

    @else
        @foreach ($vote['voted'] as $key => $data)
            <?php $proc = round(($data * 100) / $vote['sum'], 1); ?>
            <?php $maxproc = round(($data * 100) / $vote['max']); ?>

            <b>{{ $key }}</b> (Голосов: {{ $data }})<br>
            {!! App::progressBar($maxproc, $proc.'%') !!}
        @endforeach

        Проголосовало: <b>{{ $vote['count'] }}</b><br><br>

        @if (! empty($show))
            <i class="fa fa-bar-chart"></i> <a href="/votes/{{ $vote->id }}">К вариантам</a><br>
        @endif
        <i class="fa fa-users"></i> <a href="/votes/{{ $vote->id }}/voters">Проголосовавшие</a><br>
    @endif

    <i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br>
@stop
