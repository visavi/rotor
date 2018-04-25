@extends('layout')

@section('title')
    Статистика за сутки
@stop

@section('content')

    <h1>Статистика за сутки</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/counter">Статистика посещений</a></li>
            <li class="breadcrumb-item active">Статистика за сутки</li>
        </ol>
    </nav>

    @if ($currhour > 0)

        <b>Время — Хосты / Хиты</b><br>
        @for ($i = 0, $tekhours = $hours; $i < $currhour; $tekhours -= 1, $i++)
            {{ dateFixed(floor(($tekhours-1) * 3600), 'H:i') }} - {{ dateFixed(floor($tekhours * 3600), 'H:i') }} — <b>{{  $host_data[$tekhours] }}</b> / <b>{{ $hits_data[$tekhours] }}</b><br>
        @endfor

        <br>
        {{ $metrika->getCounterDay() }}
    @else
        {!! showError('Статистика за текущие сутки еще не обновилась!') !!}
    @endif
@stop
