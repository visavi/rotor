@extends('layout')

@section('title')
    Кости
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item active">Кости</li>
        </ol>
    </nav>

    <h1>Кости</h1>

    <img src="/assets/modules/games/dice/6.gif" alt="image"> и <img src="/assets/modules/games/dice/6.gif" alt="image"><br><br>

    <a class="btn btn-primary" href="/games/dice/go?rand={{ random_int(1000, 99999) }}">Играть</a><br><br>

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br><br>

    Для участия в игре нажмите "Играть"<br>
    За каждый выигрыш вы получите {{ plural(10, setting('moneyname')) }}<br>
    За каждый проигрыш у вас будут списывать по {{ plural(5, setting('moneyname')) }}<br>
    Итак дерзайте!<br>
@stop
