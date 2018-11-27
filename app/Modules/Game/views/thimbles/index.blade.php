@extends('layout')

@section('title')
    Наперстки
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item active">Наперстки</li>
        </ol>
    </nav>

    <h1>Наперстки</h1>

    <img src="/assets/img/games/thimbles/1.gif" alt="image"><br><br>

    <a class="btn btn-primary" href="/games/thimbles/choice">Играть</a><br><br>

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br><br>

    Для участия в игре нажмите "Играть"<br>
    За каждый выигрыш вы получите {{ plural(50, setting('moneyname')) }}<br>
    За каждый проигрыш у вас будут списывать по {{ plural(100, setting('moneyname')) }}<br>
    Итак дерзайте!<br>
@stop
