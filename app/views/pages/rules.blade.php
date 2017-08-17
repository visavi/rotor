@extends('layout')

@section('title')
    Правила сайта - @parent
@stop

@section('content')

    <h1>Правила сайта</h1>

    @if ($rules)
        {!! App::bbCode($rules['text']) !!}<br>
    @else
        {{ show_error('Правила сайта еще не установлены!') }}
    @endif
@stop
