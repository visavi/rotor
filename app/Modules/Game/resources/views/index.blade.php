@extends('layout')

@section('title')
    Игры / Развлечения
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Игры / Развлечения</li>
        </ol>
    </nav>

    <h1>Игры / Развлечения</h1>

    <i class="far fa-circle fa-lg text-muted"></i> <a href="/games/blackjack">21 (Очко)</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/games/dice">Кости</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/games/thimbles">Наперстки</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/games/bandit">Однорукий бандит</a><br>
@stop
