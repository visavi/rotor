@extends('layout')

@section('title')
    Статистика за месяц
@stop

@section('content')

    <h1>Статистика за месяц</h1>

    @if ($currday > 1)

        <b>Дата — Хосты / Хиты</b><br>
        @for ($i = 1, $tekdays = $days; $i < $currday; $tekdays -= 1, $i++)
            {{ dateFixed(floor(($tekdays-1) * 86400), 'd.m') }} - {{ dateFixed(floor($tekdays * 86400), 'd.m') }} — <b>{{ $host_data[$tekdays] }}</b> / <b>{{ $hits_data[$tekdays] }}</b><br>
        @endfor

        <br>
        <?php include_once(APP.'/Includes/counter31.php') ?>
    @else
        {{ showError('Статистика за текущий месяц еще не обновилась!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/counter">Вернуться</a><br>
@stop
