@extends('layout')

@section('title')
    {{ trans('index.search_users') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">Пользователи</a></li>
            <li class="breadcrumb-item active">{{ trans('index.search_users') }}</li>
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
