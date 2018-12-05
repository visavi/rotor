@extends('layout')

@section('title')
    Однорукий бандит
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item active">Однорукий бандит</li>
        </ol>
    </nav>

    <h1>Однорукий бандит</h1>

    Любишь азарт? А выигрывая, чувствуешь адреналин? Играй и получай призы<br><br>

    <img src="/assets/modules/games/bandit/1.gif" alt="image"> <img src="/assets/modules/games/bandit/2.gif" alt="image"> <img src="/assets/modules/games/bandit/3.gif" alt="image"><br>
    <img src="/assets/modules/games/bandit/8.gif" alt="image"> <img src="/assets/modules/games/bandit/8.gif" alt="image"> <img src="/assets/modules/games/bandit/8.gif" alt="image"><br>
    <img src="/assets/modules/games/bandit/5.gif" alt="image"> <img src="/assets/modules/games/bandit/6.gif" alt="image"> <img src="/assets/modules/games/bandit/7.gif" alt="image"><br><br>

    <a class="btn btn-primary" href="/games/bandit/go?rand={{ random_int(1000, 99999) }}">Играть</a><br><br>

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br><br>

    <i class="fa fa-question-circle"></i> <a href="/games/bandit/faq">Правила игры</a><br>
@stop
