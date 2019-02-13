@extends('layout')

@section('title')
    Администрация сайта
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">Администрация сайта</li>
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

                    <b>{!! $user->getProfile() !!}</b>
                    ({{ $user->getLevel() }})<br>

                    @if (isAdmin('boss'))
                        <i class="fa fa-pencil-alt"></i> <a href="/admin/users/edit?user={{ $user->login }}">Изменить</a><br>
                    @endif
                </div>
            @endforeach
        </div>

        Всего в администрации: <b>{{ $users->count() }}</b><br><br>

    @else
        {!! showError('Администрации еще нет!') !!}
    @endif
@stop
