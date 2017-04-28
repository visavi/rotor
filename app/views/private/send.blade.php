@extends('layout')

@section('title')
    Новое сообщение - @parent
@stop

@section('content')

    <h1>Новое сообщение</h1>

    <?php

    if ($user) {

        echo '<i class="fa fa-envelope"></i> Сообщение для <b>' . profile($user) . '</b> ' . user_visit($user) . ':<br />';
        echo '<i class="fa fa-history"></i> <a href="/private/history?user=' . $user->login . '">История переписки</a><br /><br />';

        echo '<div class="form">';
        echo '<form action="/private/send?user='.$user->login.'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

        echo '<textarea cols="25" rows="5" name="msg" id="markItUp"></textarea><br />';

        if (App::user('point') < App::setting('privatprotect')) {
            echo 'Проверочный код:<br />';
            echo '<img src="/captcha" alt="" /><br />';
            echo '<input name="provkod" size="6" maxlength="6" /><br />';
        }

        echo '<input value="Отправить" type="submit" /></form></div><br />';

    } else {

        echo '<div class="form">';
        echo '<form action="/private/send" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

        echo 'Введите логин:<br />';
        echo '<input type="text" name="user" maxlength="20" /><br />';

        $contacts = Contact::where('user_id', App::getUserId())
            ->rightJoin('users', 'contact.contact_id', '=', 'users.id')
            ->orderBy('users.login')
            ->get();

        if (count($contacts) > 0) {
            echo 'Или выберите из списка:<br />';
            echo '<select name="contact">';
            echo '<option value="0">Список контактов</option>';

            foreach($contacts as $data) {
                echo '<option value="'.$data->getContact()->login.'">'.$data->getContact()->login.'</option>';
            }
            echo '</select><br />';
        }

        echo '<textarea cols="25" rows="5" name="msg" id="markItUp"></textarea><br />';

        if (App::user('point') < App::setting('privatprotect')) {
            echo 'Проверочный код:<br />';
            echo '<img src="/captcha" alt="" /><br />';
            echo '<input name="provkod" size="6" maxlength="6" /><br />';
        }

        echo '<input value="Отправить" type="submit" /></form></div><br />';

        echo 'Введите логин или выберите пользователя из своего контакт-листа<br />';
    }
?>

    <i class="fa fa-arrow-circle-up"></i> <a href="/private">К письмам</a><br />
    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br />
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br />
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br />

@stop
