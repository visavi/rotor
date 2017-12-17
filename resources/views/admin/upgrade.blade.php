@extends('layout')

@section('title')
    Апгрейд системы
@stop

@section('content')

    <h1>Апгрейд системы</h1>

    <pre>
        <span class="inner-pre" style="font-size: 11px">
            <?= $wrap->getMigrate(); ?>
        </span>
    </pre>

    <i class="fa fa-check"></i> <b>Установлена актуальная версия RotorCMS</b><br><br>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
