@extends('layout')

@section('title')
    Онлайн пользователей
@stop

@section('content')

    <h1>Онлайн пользователей</h1>

    <div class="b"><b>Кто на сайте:</b></div>

    @if ($online->isNotEmpty())

        @foreach($online as $key => $value)
            {{ $comma = (empty($key)) ? '' : ', ' }}
            {!! $value->user->getGender() !!} <b>{!! profile($value->user) !!}</b>
        @endforeach

        <br>Всего пользователей: {{ $online->count() }} чел.<br><br>
    @else
        {!! showError('Зарегистированных пользователей нет!') !!}
    @endif

    <div class="b"><b>Поздравляем именинников:</b></div>

    @if ($birthdays->isNotEmpty())

        @foreach($birthdays as $key => $value)
            {{ $comma = (empty($key)) ? '' : ', ' }}
            {!! $value->getGender() !!} <b>{!! profile($value) !!}</b>
        @endforeach

        <br>Всего именниников: {{ $birthdays->count() }} чел.<br><br>
    @else
        {!! showError('Сегодня именинников нет!') !!}
    @endif

    <div class="b"><b>Приветствуем новичков:</b></div>

    @if ($novices->isNotEmpty())
        @foreach($novices as $key => $value)
            {{ $comma = (empty($key)) ? '' : ', ' }}
            {!! $value->getGender() !!} <b>{!! profile($value) !!}</b>
        @endforeach

        <br>Всего новичков: {{ $novices->count() }} чел.<br><br>
    @else
        {!! showError('Новичков пока нет!') !!}
    @endif

@stop
