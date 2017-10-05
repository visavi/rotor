@extends('layout')

@section('title')
    {{ $vote->title }}
@stop

@section('content')

    <h1>{{ $vote->title }}</h1>

    @foreach ($vote['voted'] as $key => $data)
        <?php $proc = round(($data * 100) / $vote['sum'], 1); ?>
        <?php $maxproc = round(($data * 100) / $vote['max']); ?>

        <b>{{ $key }}</b> (Голосов: {{ $data }})<br>
        {!! progressBar($maxproc, $proc.'%') !!}
    @endforeach

    Вариантов: <b>{{ count($vote['voted']) }}</b><br>
    Проголосовало: <b>{{ $vote['count'] }}</b><br><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/votes/history">Вернуться</a><br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br>
@stop
