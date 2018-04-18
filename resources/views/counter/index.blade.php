@extends('layout')

@section('title')
    Количество посещений
@stop

@section('content')

    <h1>Количество посещений</h1>

    Всего посетителей на сайте: <b>{{ $online[1] }}</b><br>
    Всего авторизованных: <b>{{ $online[0] }}</b><br>
    Всего гостей: <b>{{ ($online[1] - $online[0]) }}</b><br><br>

    Хостов сегодня: <b>{{ $count->dayhosts }}</b><br>
    Хитов сегодня: <b>{{ $count->dayhits }}</b><br>
    Всего хостов: <b>{{ $count->allhosts }}</b><br>
    Всего хитов: <b>{{ $count->allhits }}</b><br><br>

    Хостов за текущий час: <b>{{ $count->hosts24 }}</b><br>
    Хитов за текущий час: <b>{{ $count->hits24 }}</b><br><br>

    Хостов за 24 часа: <b>{{ ($counts24->hosts + $count->hosts24) }}</b><br>
    Хитов за 24 часа: <b>{{ ($counts24->hits + $count->hits24) }}</b><br><br>

    Хостов за месяц: <b>{{ ($counts31->hosts + $count->dayhosts) }}</b><br>
    Хитов за месяц: <b>{{ ($counts31->hits + $count->dayhits) }}</b><br><br>

    Динамика за неделю<br>
    {{ $metrika->getCounterWeek() }}

    Динамика за сутки<br>
    {{ $metrika->getCounterDay() }}

    Динамика за месяц<br>
    {{ $metrika->getCounterMonth() }}

    <a href="/counter/day">Статистика за сутки</a><br>
    <a href="/counter/month">Статистика за месяц</a><br><br>
@stop
