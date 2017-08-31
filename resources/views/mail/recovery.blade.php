@extends('layout')

@section('title')
    Восстановление пароля - @parent
@stop

@section('content')

    <h1>Восстановление пароля</h1>

    <div class="form">
        <form method="post" action="/recovery">
            Логин или email:<br>
            <input name="user" value="{{ $cookieLogin }}" maxlength="100"><br>
            Проверочный код:<br>
            <img src="/captcha" onclick="this.src='/captcha?'+Math.random()" class="rounded" alt="" style="cursor: pointer;" alt=""><br>
            <input name="protect" size="6" maxlength="6"><br>
            <br><input value="Восстановить" type="submit"></form>
    </div><br>

    Письмо с инструкцией по восстановлению пароля будет выслано на email указанный в профиле<br>
    Внимательно прочтите письмо и выполните все необходимые действия<br>
    Восстанавливать пароль можно не чаще чем раз в 12 часов<br><br>

@stop
