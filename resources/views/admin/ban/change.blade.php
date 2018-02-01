@extends('layout')

@section('title')
    Бан пользователя {{ $user->login }}
@stop

@section('content')

    <h1>Изменение бана пользователя {{ $user->login }}</h1>

    <h3>{!! $user->getGender() !!} {!! profile($user) !!}</h3>



    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/ban/edit?user={{ $user->login }}">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
