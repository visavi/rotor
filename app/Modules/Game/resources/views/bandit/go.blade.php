@extends('layout')

@section('title')
    Ваш ход
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item"><a href="/games/bandit">Однорукий бандит</a></li>
            <li class="breadcrumb-item active">Ваш ход</li>
        </ol>
    </nav>

    <h1>Ваш ход</h1>

    <img src="/assets/modules/games/bandit/{{ $num[1] }}.gif" alt="image"> <img src="/assets/modules/games/bandit/{{ $num[2] }}.gif" alt="image"> <img src="/assets/modules/games/bandit/{{ $num[3] }}.gif" alt="image"><br>

    <img src="/assets/modules/games/bandit/{{ $num[4] }}.gif" alt="image"> <img src="/assets/modules/games/bandit/{{ $num[5] }}.gif" alt="image"> <img src="/assets/modules/games/bandit/{{ $num[6] }}.gif" alt="image"><br>

    <img src="/assets/modules/games/bandit/{{ $num[7] }}.gif" alt="image"> <img src="/assets/modules/games/bandit/{{ $num[8] }}.gif" alt="image"> <img src="/assets/modules/games/bandit/{{ $num[9] }}.gif" alt="image"><br><br>

    @if ($sum > 0)
        @foreach ($results as $result)
            {{ $result }}<br>
        @endforeach

        <i class="fas fa-trophy"></i> Ваш выигрыш составил: <b>{{ plural($sum, setting('moneyname')) }}</b><br><br>
    @endif

    <a class="btn btn-primary" href="/games/bandit/go?rand={{ mt_rand(1000, 99999) }}">Играть</a><br><br>

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br>
@stop
