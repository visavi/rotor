@extends('layout')

@section('title')
    Результат поиска
@stop

@section('content')

    <h1>Результат поиска</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/searchusers">Поиск пользователей</a></li>
            <li class="breadcrumb-item active">Результат поиска</li>
        </ol>
    </nav>

    @if ($users->isNotEmpty())
        @foreach($users as $user)
            {!! $user->getGender() !!}
            <b>{!! profile($user) !!}</b> {!! userOnline($user) !!} ({{ plural($user->point, setting('scorename')) }})<br>
        @endforeach

        <br>Найдено совпадений: <b>{{ $users->count() }}</b><br><br>
    @else
        {!! showError('По вашему запросу ничего не найдено') !!}
    @endif
@stop
