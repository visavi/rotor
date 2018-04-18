@extends('layout')

@section('title')
    Результат поиска
@stop

@section('content')

    <h1>Результат поиска</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/searchuser">Поиск пользователей</a></li>
            <li class="breadcrumb-item active">Результат поиска</li>
        </ol>
    </nav>

    @if ($users->isNotEmpty())

        @foreach ($users as $user)
            {!! $user->getGender() !!} <b>{!! profile($user) !!}</b>
            {!! userOnline($user) !!} ({{ plural($user->point, setting('scorename')) }})<br>
        @endforeach

        <br>
        {!! pagination($page) !!}

        Найдено совпадений: {{ $page->total }}<br><br>

    @else
        {!! showError('Совпадений не найдено!') !!}
    @endif
@stop
