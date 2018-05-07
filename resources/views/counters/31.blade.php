@extends('layout')

@section('title')
    Статистика за месяц
@stop

@section('content')

    <h1>Статистика за месяц</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/counters">Статистика посещений</a></li>
            <li class="breadcrumb-item active">Статистика за месяц</li>
        </ol>
    </nav>

    @if ($currday > 1)

        <b>Дата — Хосты / Хиты</b><br>
        @for ($i = 1, $tekdays = $days; $i < $currday; $tekdays -= 1, $i++)
            {{ dateFixed(floor(($tekdays-1) * 86400), 'd.m') }} - {{ dateFixed(floor($tekdays * 86400), 'd.m') }} — <b>{{ $host_data[$tekdays] }}</b> / <b>{{ $hits_data[$tekdays] }}</b><br>
        @endfor

        <br>
        {{ $metrika->getCounterMonth() }}
    @else
        {!! showError('Статистика за текущий месяц еще не обновилась!') !!}
    @endif
@stop
