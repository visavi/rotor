@extends('layout')

@section('title')
    Взлом сейфа
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item active">Взлом сейфа</li>
        </ol>
    </nav>

    <h1>Взлом сейфа</h1>

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br><br>

    {!! $user->getProfile(null, false) !!}, не торопись! Просто хорошо подумай<br>
    <br><img src="/assets/modules/games/safe/safe-closed.png" alt="сейф"><br>

    Всё готово для совершения взлома! Введите комбинацию цифр и нажмите ломать сейф!<br><br>

    Комбинация сейфа:<br>
    <span class="badge badge-info">-</span>
    <span class="badge badge-info">-</span>
    <span class="badge badge-info">-</span>
    <span class="badge badge-info">-</span>
    <span class="badge badge-info">-</span>

    <div class="form">
        <form action="/games/safe/go" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="form-group row{{ hasError('code') }}">
                <div class="col-1">
                    <input class="form-control" name="code0" maxlength="1" value="{{ getInput('code0') }}" required>
                </div>
                <div class="col-1">
                    <input class="form-control" name="code1" maxlength="1" value="{{ getInput('code1') }}" required>
                </div>
                <div class="col-1">
                    <input class="form-control" name="code2" maxlength="1" value="{{ getInput('code2') }}" required>
                </div>
                <div class="col-1">
                    <input class="form-control" name="code3" maxlength="1" value="{{ getInput('code3') }}" required>
                </div>
                <div class="col-1">
                    <input class="form-control" name="code4" maxlength="1" value="{{ getInput('code4') }}" required>
                </div>
            </div>
            <button class="btn btn-primary">Ломать сейф</button>
        </form>
    </div><br>

    Попробуй вскрыть наш сейф.<br>
    В сейфе тебя ждёт: {{ plural(1000, setting('moneyname')) }}<br>
    За попытку взлома ты заплатишь {{ plural(100, setting('moneyname')) }}<br>
    Платишь 1 paз зa 5 попыток. Ну это чтобы купить себе необходимое для взлома оборудование.<br>
    У тебя будет только 5 попыток чтобы подобрать код из 5-х цифр.<br>
    Если тебя это устраивает, то ВПЕРЁД!<br>
@stop
