@extends('layout')

@section('title')
    Администрация сайта
@stop

@section('content')

    <h1>Администрация сайта</h1>

    @if ($users->isNotEmpty())

        @foreach($users as $user)
            {!! $user->getGender() !!} <b>{!! profile($user) !!}</b>
            ({{ userLevel($user->level) }}) {!! userOnline($user) !!}<br>

            @if (isAdmin('boss'))
                <i class="fa fa-pencil-alt"></i> <a href="/admin/users/edit?user={{ $user->login }}">Изменить</a><br>
            @endif
        @endforeach

        <br>Всего в администрации: <b>{{ $users->count() }}</b><br><br>

    @else
        {!! showError('Администрации еще нет!') !!}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
