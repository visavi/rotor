@extends('layout')

@section('title')
    Восстановление пароля
@stop

@section('content')

    <h1>Восстановление пароля</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Восстановление пароля</li>
        </ol>
    </nav>

    <div class="form">
        <form method="post" action="/recovery">

            <div class="form-group{{ hasError('user') }}">
                <label for="inputUser">Логин или email:</label>
                <input class="form-control" name="user" id="inputUser" value="{{ getInput('user', $cookieLogin) }}" maxlength="100" required>
                {!! textError('user') !!}
            </div>

            {!! view('app/_captcha') !!}

            <button class="btn btn-primary">Восстановить</button>
        </form>
    </div><br>

    Письмо с инструкцией по восстановлению пароля будет выслано на email указанный в профиле<br>
    Внимательно прочтите письмо и выполните все необходимые действия<br>
    Восстанавливать пароль можно не чаще чем раз в 12 часов<br><br>
@stop
