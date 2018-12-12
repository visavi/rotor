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

    Комбинация сейфа:<br>
    <span class="badge badge-info">{{ $hack[0] }}</span>
    <span class="badge badge-info">{{ $hack[1] }}</span>
    <span class="badge badge-info">{{ $hack[2] }}</span>
    <span class="badge badge-info">{{ $hack[3] }}</span>
    <span class="badge badge-info">{{ $hack[4] }}</span>
    <br><br>

    @if (implode($safe['cipher']) === implode($hack))
        <img src="/assets/modules/games/safe/safe-open.png" alt="сейф"><br><br>
        ПОЗДРАВЛЯЮ! СЕЙФ УСПЕШНО ВЗЛОМАН!<br>
        НА ВАШ СЧЁТ ПЕРЕВЕДЕНЫ {{ plural(1000, setting('moneyname')) }}<br><br>

        <a href="/games/safe">Ещё взломать?</a><br><br>
    @else
        @if ($safe['try'])
            {!! $user->getProfile(null, false) !!}, не торопись! Просто хорошо подумай<br>
            Попыток осталось: {{ $safe['try'] }}<br>

            <img src="/assets/modules/games/safe/safe-closed.png" alt="сейф"><br>

            <div class="form">
                <form action="/games/safe/go" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                    <div class="form-group row{{ hasError('bet') }}">
                        <div class="col-1">
                            <input class="form-control" name="code0" maxlength="1" value="{{ getInput('code0', $hack[0] === $safe['cipher'][0] ? $safe['cipher'][0] : '') }}" required>
                        </div>
                        <div class="col-1">
                            <input class="form-control" name="code1" maxlength="1" value="{{ getInput('code1', $hack[1] === $safe['cipher'][1] ? $safe['cipher'][1] : '') }}" required>
                        </div>
                        <div class="col-1">
                            <input class="form-control" name="code2" maxlength="1" value="{{ getInput('code2', $hack[2] === $safe['cipher'][2] ? $safe['cipher'][2] : '') }}" required>
                        </div>
                        <div class="col-1">
                            <input class="form-control" name="code3" maxlength="1" value="{{ getInput('code3', $hack[3] === $safe['cipher'][3] ? $safe['cipher'][3] : '') }}" required>
                        </div>
                        <div class="col-1">
                            <input class="form-control" name="code4" maxlength="1" value="{{ getInput('code4', $hack[4] === $safe['cipher'][4] ? $safe['cipher'][4] : '') }}" required>
                        </div>
                    </div>
                    <button class="btn btn-primary">Ломать сейф</button>
                </form>
            </div><br>
        @else
            <img src="/assets/modules/games/safe/safe-closed.png" alt="сейф"><br>

            Шифр был:<br/>
            <span class="badge badge-info">{{ $safe['cipher'][0] }}</span>
            <span class="badge badge-info">{{ $safe['cipher'][1] }}</span>
            <span class="badge badge-info">{{ $safe['cipher'][2] }}</span>
            <span class="badge badge-info">{{ $safe['cipher'][3] }}</span>
            <span class="badge badge-info">{{ $safe['cipher'][4] }}</span>
            <br><br>
            Попытки закончились. A взломать сейф так и не получилось...<br>
            Возможно, в другой раз тебе повезёт больше...<br><br>

            <a href="/games/safe">Ещё разок!</a><br><br>
        @endif
    @endif

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br>

    <hr>
    Справка:<br>
    1. символ <b>-</b> означает, что введённая цифра отсутствует в коде сейфа<br>
    2. символ <b>*</b> означает, что цифра, которую вы ввели есть, но стоит на другом месте в шифре сейфа<br>
    3. символ <b>х</b> означает, что хотябы одна из угаданных вами цифр присутствует в шифре сейфа, и стоит на месте <b>х</b><br>
@stop
