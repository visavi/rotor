@extends('layout')

@section('title')
    Ваш ход
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item"><a href="/games/safe">Взлом сейфа</a></li>
            <li class="breadcrumb-item active">Ваш ход</li>
        </ol>
    </nav>

    <h1>Ваш ход</h1>

    @if ($hack[0] === $safe[0] && $hack[1] === $safe[1] && $hack[2] === $safe[2] && $hack[3] === $safe[3]) {
    <img src="/assets/img/safe/safe-open.png" alt="сейф"/><br>';
    <br>ПОЗДРАВЛЯЮ! СЕЙФ УСПЕШНО ВЗЛОМАН!<br>
    <font color="red">НА ВАШ СЧЁТ ПЕРЕВЕДЕНЫ 1000$</font><br>';

    DB::run() -> query("UPDATE `users` SET `money`=`money`+? WHERE `login`=? LIMIT 1;", [$config['safesum'], $log]);
    unset($_SESSION['go'], $_SESSION['try']);

    echo'&raquo; <a href="/games/safe">Ещё взломать?</a><br><br>';
    @else
        <div class="form">
            <form action="/games/safe/go" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <div class="form-group row{{ hasError('bet') }}">
                    <div class="col-1">
                        <input class="form-control" name="code0" maxlength="1" value="{{ getInput('code0', $hack[0]) }}" required>
                    </div>
                    <div class="col-1">
                        <input class="form-control" name="code1" maxlength="1" value="{{ getInput('code1', $hack[1]) }}" required>
                    </div>
                    <div class="col-1">
                        <input class="form-control" name="code2" maxlength="1" value="{{ getInput('code2', $hack[2]) }}" required>
                    </div>
                    <div class="col-1">
                        <input class="form-control" name="code3" maxlength="1" value="{{ getInput('code3', $hack[3]) }}" required>
                    </div>
                </div>
                <button class="btn btn-primary">Ломать сейф</button>
            </form>
        </div><br>
    @endif

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br>

    <hr>
    Справка:<br>
    1. символ <b>-</b> означает, что введённая цифра отсутствует в коде сейфа.<br>
    2. символ <b>*</b> означает, что цифра, которую вы ввели есть, но стоит на другом месте в шифре сейфа.<br>
    3. символ <b>х</b> означает, что хотябы одна из угаданных вами цифр присутствует в шифре сейфа, и стоит на месте <b>х</b>.<br><br>';
@stop
