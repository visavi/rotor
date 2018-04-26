@extends('layout')

@section('title')
    Администрация сайта
@stop

@section('content')

    <h1>Администрация сайта</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Администрация сайта</li>
        </ol>
    </nav>

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
@stop
