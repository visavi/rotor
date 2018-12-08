@extends('layout')

@section('title')
    21 (Очко)
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item active">21 (Очко)</li>
        </ol>
    </nav>

    <h1>21 (Очко)</h1>

    <img src="/assets/modules/games/cards/18.png" alt="image"> <img src="/assets/modules/games/cards/34.png" alt="image"><br><br>

    @if (empty($_SESSION['blackjack']['bet']))
        <div class="form">
            <form action="/games/blackjack/bet" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('bet') }}">
                    <label for="bet">Ваша ставка:</label>
                    <input class="form-control" name="bet" id="bet" value="{{ getInput('bet') }}" required>
                    {!! textError('bet') !!}
                </div>

                <button class="btn btn-primary">Играть</button>
            </form>
        </div><br>
    @else
        Ставки сделаны, на кону: {{ plural($_SESSION['blackjack']['bet'] * 2, setting('moneyname')) }}<br><br>
        <b><a href="/games/blackjack/game?rand={{ mt_rand(1000, 99999) }}'">Вернитесь в игру</a></b><br><br>
    @endif

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br><br>

    <i class="fa fa-question-circle"></i> <a href="/games/blackjack/rules">Правила игры</a><br>
@stop
