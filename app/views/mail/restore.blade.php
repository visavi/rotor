@extends('layout')

@section('title')
    Восстановление пароля - @parent
@stop

@section('content')

    <h1>Восстановление пароля</h1>

<?php
    echo '<b>Пароль успешно восстановлен!</b><br />';
    echo 'Ваши новые данные для входа на сайт<br /><br />';

    echo 'Логин: <b>'.$login.'</b><br />';
    echo 'Пароль: <b>'.$password.'</b><br /><br />';

    echo 'Запомните и постарайтесь больше не забывать данные<br /><br />';

    echo 'Пароль вы сможете поменять в своем профиле<br /><br />';

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/recovery">Вернуться</a><br />';
    ?>
@stop
