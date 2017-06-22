@extends('layout')

@section('title')
    Восстановление пароля - @parent
@stop

@section('content')

    <h1>Восстановление пароля</h1>

<?php
    echo '<div class="form">';
    echo '<form method="post" action="/recovery">';
    echo 'Логин или email:<br />';
    echo '<input name="user" type="text" value="'.$cookieLogin.'" maxlength="100" /><br />';
    echo 'Проверочный код:<br /> ';
    echo '<img src="/captcha" onclick="this.src=\'/captcha?\'+Math.random()" class="img-rounded" alt="" style="cursor: pointer;" alt="" /><br />';
    echo '<input name="protect" size="6" maxlength="6" /><br />';
    echo '<br /><input value="Восстановить" type="submit" /></form></div><br />';

    echo 'Письмо с инструкцией по восстановлению пароля будет выслано на email указанный в профиле<br />';
    echo 'Внимательно прочтите письмо и выполните все необходимые действия<br />';
    echo 'Восстанавливать пароль можно не чаще чем раз в 12 часов<br /><br />';
    ?>
@stop
