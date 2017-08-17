@extends('layout')

@section('title')
    Обратная связь - @parent
@stop

@section('content')

    <h1>Обратная связь</h1>

<?php
    echo '<div class="form">';
        echo '<form method="post" action="/mail">';

            if (! is_user()) {
            echo 'Ваше имя:<br><input name="name" maxlength="20"><br>';
            echo 'Ваш email:<br><input name="email" maxlength="50"><br>';
            } else {
            if (empty(App::user('email'))) {
            echo 'Ваш email:<br><input name="email" maxlength="50"><br>';
            }
            }

            echo 'Сообщение:<br>';
            echo '<textarea cols="25" rows="5" name="message"></textarea><br>';

            echo 'Проверочный код:<br>';
            echo '<img src="/captcha" onclick="this.src=\'/captcha?\'+Math.random()" class="img-rounded" alt="" style="cursor: pointer;" alt=""><br>';

            echo '<input name="protect" size="6" maxlength="6"><br>';
            echo '<input value="Отправить" type="submit"></form></div><br>';

    echo 'Обновите страницу если вы не видите проверочный код!<br><br>';
?>
@stop
