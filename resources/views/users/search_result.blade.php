@extends('layout')

@section('title')
    Результат поиска
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/searchusers">Поиск пользователей</a></li>
            <li class="breadcrumb-item active">Результат поиска</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())
        <div class="mb-3">
            @foreach($users as $user)
                <div  class="text-truncate bg-light my-1">
                    <div class="img">
                        {!! $user->getAvatar() !!}
                        {!! $user->getOnline() !!}
                    </div>

                    <b><a href="/admin/users/edit?user={{ $user->login }}">{{ $user->login }}</a></b>
                    ({{ plural($user->point, setting('scorename')) }})
                </div>
            @endforeach
        </div>

        Найдено совпадений: <b>{{ $users->count() }}</b><br><br>
    @else
        {!! showError('По вашему запросу ничего не найдено') !!}
    @endif
@stop
