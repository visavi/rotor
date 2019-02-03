@extends('layout')

@section('title')
    Восстановление пароля
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Восстановление пароля</li>
        </ol>
    </nav>
@stop

@section('content')
    <b>Пароль успешно восстановлен!</b><br>
    Ваши новые данные для входа на сайт<br><br>

    Логин: <b>{{ $login }}</b><br>
    Пароль: <b>{{ $password }}</b><br><br>

    Запомните и постарайтесь больше не забывать данные<br><br>

    Пароль вы сможете поменять в своем профиле<br><br>
@stop
