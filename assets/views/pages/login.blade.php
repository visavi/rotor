@extends('layout')

@section('title', 'Авторизация - @parent')

@section('content')

    <h1>Авторизация</h1>
    <div class="form">
        <form method="post" action="/login">


            <div class="form-group">
                <label for="inputLogin">Логин или ник:</label>
                <input class="form-control" name="login" id="inputLogin" maxlength="20" value="{{ App::getInput('login') }}" required>

                <label for="inputPassword">Пароль:</label>
                <input class="form-control" name="pass" type="password" id="inputPassword" maxlength="20" required>
                <label>
                    <input name="remember" type="checkbox" value="1" checked="checked" /> Запомнить меня
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Войти</button>
        </form>
    </div>
    <br />
    <a href="register">Регистрация</a><br />
    <a href="/mail/lostpassword.php">Забыли пароль?</a><br /><br />
@stop
