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
        <div class="mb-3">
            @foreach($users as $user)
                <div  class="text-truncate bg-light my-1">
                    <div class="img">
                        {!! $user->getAvatar() !!}
                        {!! $user->getOnline() !!}
                    </div>

                    <b><a href="/admin/users/edit?user={{ $user->login }}">{{ $user->login }}</a></b>
                    ({{ plural($user->point, setting('scorename')) }})<br>

                    @if ($user->name )
                        Имя: {{ $user->name }}
                    @endif
                </div>
            @endforeach
        </div>

        {!! pagination($page) !!}

        Найдено совпадений: {{ $page->total }}<br><br>

    @else
        {!! showError('Пользователи не найдены!') !!}
    @endif
@stop
