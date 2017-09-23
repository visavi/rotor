@extends('layout')

@section('title')
    Подтверждение регистрации - @parent
@stop

@section('content')

    <h1>Подтверждение регистрации</h1>
    Добро пожаловать, <b>{{ getUser('login') }}!</b><br>
    Для подтверждения регистрации вам необходимо ввести мастер-ключ, который был отправлен вам на email<br><br>

    <div class="form">
        Код подтверждения:<br>
        <form method="get" action="/key">
            <input class="form-control" name="code" maxlength="30">
            <button class="btn btn-primary">Подтвердить</button>
        </form>
    </div><br>

    Пока вы не подтвердите регистрацию вы не сможете войти на сайт<br>
    Ваш профиль будет ждать активации в течение 24 часов, после чего автоматически удален<br><br>

    <i class="fa fa-times"></i> <a href="/logout">Выход</a><br>
@stop
