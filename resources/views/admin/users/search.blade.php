@extends('layout')

@section('title')
    Поиск пользователей
@stop

@section('content')

    <h1>Поиск пользователей</h1>

    @if ($users->isNotEmpty())

        @foreach ($users as $user)
            {!! $user->getGender() !!} <a href="/admin/users/edit?user={{ $user->login }}">{{ $user->login }}</a>
            {!! userOnline($user) !!}  ({{ plural($user->point, setting('scorename')) }})<br>
        @endforeach

        {!! pagination($page) !!}

        Найдено совпадений: {{ $page->total }}<br><br>

    @else
        {!! showError('Пользователи не найдены!') !!}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/users">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
