@extends('layout')

@section('title')
    Правила сайта
@stop

@section('content')

    <h1>Правила сайта</h1>

    @if ($rules)
        {!! bbCode($rules['text']) !!}<br>
    @else
        {!! showError('Правила сайта еще не установлены!') !!}
    @endif
@stop
