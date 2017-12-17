@extends('layout')

@section('title')
    Апгрейд системы
@stop

@section('content')

    <h1>Апгрейд системы</h1>

    {!! nl2br($wrap->getMigrate()) !!}

    <br>
    <div class="alert alert-success">
        <i class="fa fa-check"></i> <b>Установлена актуальная версия RotorCMS</b>
    </div>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
