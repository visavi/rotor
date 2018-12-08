@extends('layout')

@section('title')
    Ваш ход
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item"><a href="/games/guess">Угадай число</a></li>
            <li class="breadcrumb-item active">Ваш ход</li>
        </ol>
    </nav>

    <h1>Ваш ход</h1>

    <b>Введите число от 1 до 100</b><br><br>

    @if (1 === 1)
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



    echo '<i class="fa fa-times"></i> <b>Вы проигали потому что, не отгадали число за 5 попыток</b><br />';
    echo 'Было загадано число: '.$_SESSION['hill'].'<br /><br />';


    @else
        echo '<b>Поздравляем!!! Вы угадали число '.$guess.'</b><br />';
        echo 'Ваш выигрыш составил 100<br /><br />';
    @endif

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br>
@stop
