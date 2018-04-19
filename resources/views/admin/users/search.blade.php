@extends('layout')

@section('title')
    Поиск пользователей
@stop

@section('content')

    <h1>Поиск пользователей</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">Пользователи</a></li>
            <li class="breadcrumb-item active">Поиск пользователей</li>
        </ol>
    </nav>

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
@stop
