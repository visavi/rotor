@extends('layout')

@section('title')
    Правила сайта
@stop

@section('content')

    <h1>Правила сайта</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Правила сайта</li>
        </ol>
    </nav>

    @if ($rules)
        {!! bbCode($rules['text']) !!}<br>
    @else
        {!! showError('Правила сайта еще не установлены!') !!}
    @endif
@stop
