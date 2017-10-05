@extends('layout')

@section('title')
    Статистика за сутки
@stop

@section('content')

    <h1>Статистика за сутки</h1>

    @if ($currhour > 0)

        <b>Время — Хосты / Хиты</b><br>
        @for ($i = 0, $tekhours = $hours; $i < $currhour; $tekhours -= 1, $i++)
            {{ dateFixed(floor(($tekhours-1) * 3600), 'H:i') }} - {{ dateFixed(floor($tekhours * 3600), 'H:i') }} — <b>{{  $host_data[$tekhours] }}</b> / <b>{{ $hits_data[$tekhours] }}</b><br>
        @endfor

        <br>
        <?php include_once(APP.'/Includes/counter24.php') ?>
    @else
        {{ showError('Статистика за текущие сутки еще не обновилась!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/counter">Вернуться</a><br>
@stop
