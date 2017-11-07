@extends('layout')

@section('title')
    Восстановление пароля
@stop

@section('content')

    <h1>Восстановление пароля</h1>

    <div class="form">
        <form method="post" action="/recovery">

            <div class="form-group{{ hasError('user') }}">
                <label for="inputUser">Логин или email:</label>
                <input class="form-control" name="user" id="inputUser" value="{{ getInput('user', $cookieLogin) }}" maxlength="100" required>
                {!! textError('user') !!}
            </div>

            <div class="form-group{{ hasError('protect') }}">
                <label for="inputProtect">Проверочный код:</label><br>
                <img src="/captcha" onclick="this.src='/captcha?'+Math.random()" class="rounded" alt="" style="cursor: pointer;" alt=""><br>

                <input type="text" class="form-control" id="inputProtect" name="protect" maxlength="6" required>
                {!! textError('protect') !!}
            </div>

            <button class="btn btn-primary">Восстановить</button>
        </form>
    </div><br>

    Письмо с инструкцией по восстановлению пароля будет выслано на email указанный в профиле<br>
    Внимательно прочтите письмо и выполните все необходимые действия<br>
    Восстанавливать пароль можно не чаще чем раз в 12 часов<br><br>

@stop
