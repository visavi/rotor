@extends('layout')

@section('title')
    Угадай число
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item active">Угадай число</li>
        </ol>
    </nav>

    <h1>Угадай число</h1>

    <b>Введите число от 1 до 100</b><br><br>

    <div class="form">
        <form action="/games/guess/go" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('guess') }}">
                <label for="guess">Введите число:</label>
                <input class="form-control" name="guess" id="guess" value="{{ getInput('guess') }}" required>
                {!! textError('guess') !!}
            </div>

            <button class="btn btn-primary">Угадать</button>
        </form>
    </div><br>

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br><br>

    Для участия в игре введите число и нажмите "Угадать"<br>
    За каждую попытку у вас будут списывать по {{ plural(3, setting('moneyname')) }}<br>
    После каждой попытки вам дают подсказку большое это число или маленькое<br>
    Если вы не уложились за 5 попыток, то игра будет начата заново<br>
    При выигрыше вы получаете {{ plural(100, setting('moneyname')) }}<br>
    Итак дерзайте!<br>
@stop
