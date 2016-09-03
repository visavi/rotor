@extends('layout')

@section('title', 'Авторизация - @parent')

@section('content')

    <h1>Авторизация</h1>
    <div class="form">
        <form method="post" action="/login">
            Логин или ник:<br /><input name="login" value="{{ $cooklog }}" maxlength="20" /><br />
            Пароль:<br />
            <input name="pass" type="password" maxlength="20" /><br />
            Запомнить меня:
            <input name="cookietrue" type="checkbox" value="1" checked="checked" /><br />
            <input value="Войти" type="submit" />
        </form>
    </div>
    <br />
    <a href="register">Регистрация</a><br />
    <a href="/mail/lostpassword.php">Забыли пароль?</a><br /><br />
@stop
