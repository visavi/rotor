@extends('layout')

@section('title')
    Стена пользователя {{ $user->login }} - @parent
@stop

@section('content')

    <h1>Стена пользователя {{ $user->login }}</h1>

@stop
