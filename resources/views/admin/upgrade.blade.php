@extends('layout')

@section('title')
    Апгрейд системы
@stop

@section('content')

    <h1>Апгрейд системы</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Апгрейд системы</li>
        </ol>
    </nav>

    {!! nl2br($wrap->getMigrate()) !!}

    <br>
    <div class="alert alert-success">
        <i class="fa fa-check"></i> <b>Установлена актуальная версия Rotor</b>
    </div>
@stop
