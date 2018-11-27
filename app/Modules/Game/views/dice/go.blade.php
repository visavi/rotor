@extends('layout')

@section('title')
    Кости
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item"><a href="/games/dice">Кости</a></li>
            <li class="breadcrumb-item active">Ваш ход</li>
        </ol>
    </nav>

    <h1>Ваш ход</h1>

    Ваши кости:<br>
    <img src="/assets/img/games/dice/{{ $num[0] }}.gif" alt="image"> и <img src="/assets/img/games/dice/{{ $num[1] }}.gif" alt="image"><br><br>

    У банкира:<br>
    <img src="/assets/img/games/dice/{{ $num[2] }}.gif" alt="image"> и <img src="/assets/img/games/dice/{{ $num[3] }}.gif" alt="image"><br><br>

    <div class="font-weight-bold">
        <i class="fas fa-trophy"></i> {!! $result !!}
    </div>

    <a class="btn btn-primary" href="/games/dice/go?rand={{ mt_rand(1000, 99999) }}">Играть</a><br><br>

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br>
@stop
