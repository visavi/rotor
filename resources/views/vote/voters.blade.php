@extends('layout')

@section('title')
    Последние проголосовавшие - @parent
@stop

@section('content')

    <h1>{{ $vote->title }}</h1>

    <i class="fa fa-bar-chart"></i> Голосов: {{ $vote['count'] }}<br><br>

    @if ($voters->isNotEmpty())
        @foreach ($voters as $voter)
            {!! user_gender($voter['user']) !!} {!! profile($voter['user']) !!} ({{ date_fixed($voter['created_at']) }})<br>
        @endforeach
    @else
        {{ showError('В голосовании никто не участвовал!') }}
    @endif
    <br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/votes/{{ $vote->id }}">Вернуться</a><br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br>
@stop
