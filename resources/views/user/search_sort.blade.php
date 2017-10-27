@extends('layout')

@section('title')
    Результат поиска
@stop

@section('content')

    <h1>Результат поиска</h1>

    @if ($users->isNotEmpty())

        @foreach ($users as $user)
            {!! $user->getGender() !!} <b>{!! profile($user) !!}</b>
            {!! userOnline($user) !!} ({{ plural($user->point, setting('scorename')) }})<br>
        @endforeach

        <br>
        {{ pagination($page) }}

        Найдено совпадений: {{ $page['total'] }}<br><br>

    @else
        {{ showError('Совпадений не найдено!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/searchuser">Вернуться</a><br>
@stop
