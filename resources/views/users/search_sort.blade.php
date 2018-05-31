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
        <div class="mb-3">
            @foreach($users as $user)
                <div  class="text-truncate bg-light my-1">
                    <div class="img">
                        {!! userAvatar($user) !!}
                        {!! userOnline($user) !!}
                    </div>

                    <b><a href="/admin/users/edit?user={{ $user->login }}">{{ $user->login }}</a></b>
                    ({{ plural($user->point, setting('scorename')) }})
                </div>
            @endforeach
        </div>

        {!! pagination($page) !!}

        Найдено совпадений: {{ $page->total }}<br><br>

    @else
        {!! showError('Совпадений не найдено!') !!}
    @endif
@stop
