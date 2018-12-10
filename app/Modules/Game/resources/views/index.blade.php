@extends('layout')

@section('title')
    {{ trans('Game::games.module') }}
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('Game::games.module') }}</li>
        </ol>
    </nav>

    <h1>{{ trans('Game::games.module') }}</h1>

    <i class="far fa-circle fa-lg text-muted"></i> <a href="/games/blackjack">{{ trans('Game::games.blackjack') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/games/dices">{{ trans('Game::games.dices') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/games/thimbles">{{ trans('Game::games.thimbles') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/games/bandit">{{ trans('Game::games.bandit') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/games/guess">{{ trans('Game::games.guess') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/games/safe">{{ trans('Game::games.safe') }}</a><br>
@stop
