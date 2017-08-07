@extends('layout')

@section('title')
    Подтверждение регистрации - @parent
@stop

@section('content')
    <?php

    if (App::user('confirmreg') == 1) {

        echo '<h1>Подтверждение регистрации</h1>';
        echo 'Добро пожаловать, <b>'.App::getUsername().'!</b><br />';
        echo 'Для подтверждения регистрации вам необходимо ввести мастер-ключ, который был отправлен вам на email<br /><br />';

        echo '<div class="form">';
        echo 'Мастер-код:<br />';
        echo '<form method="get" action="/key">';
        echo '<input  class="form-control" name="code" maxlength="30" />';
        echo '<button class="btn btn-primary">Подтвердить</button></form></div><br />';

        echo 'Пока вы не подтвердите регистрацию вы не сможете войти на сайт<br />';
        echo 'Ваш профиль будет ждать активации в течение 24 часов, после чего автоматически удален<br /><br />';

    } else {
        echo '<h1>Ожидание модерации</h1>';
        echo 'Добро пожаловать, <b>'.check(App::getUsername()).'!</b><br />';
        echo 'Ваш аккаунт еще не прошел проверку администрацией<br />';
        echo 'Если после авторизации вы видите эту страницу, значит ваш профиль еще не активирован!<br /><br />';
    }

    echo '<i class="fa fa-times"></i> <a href="/logout">Выход</a><br />';
    ?>
@stop
