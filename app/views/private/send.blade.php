@extends('layout')

@section('title')
    Новое сообщение - @parent
@stop

@section('content')

    <h1>Новое сообщение</h1>

    <?php

    if ($user) {

        echo '<i class="fa fa-envelope"></i> Сообщение для <b>' . profile($user) . '</b> ' . user_visit($user) . ':<br>';
        echo '<i class="fa fa-history"></i> <a href="/private/history?user=' . $user->login . '">История переписки</a><br>';

        if (isIgnore(App::user(), $user)) {
            echo '<b><span style="color:#ff0000">Внимание, данный пользователь находится в игнор-листе!</span></b><br>';
        }

        echo '<div class="form">';
        echo '<form action="/private/send?user='.$user->login.'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
?>

        <label for="markItUp">Сообщение:</label>
        <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required></textarea>
<?php
        if (App::user('point') < Setting::get('privatprotect')) {
            echo 'Проверочный код:<br>';
            echo '<img src="/captcha" alt=""><br>';
            echo '<input name="provkod" size="6" maxlength="6"><br>';
        }

        echo '<button class="btn btn-primary">Отправить</button></form></div><br>';

    } else {

        echo '<div class="form">';
        echo '<form action="/private/send" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
?>

<label for="inputLogin">Логин:</label>
<input class="form-control" name="user" id="inputLogin" maxlength="20" value="{{ App::getInput('user') }}">

    <?php
        $contacts = Contact::where('user_id', App::getUserId())
            ->rightJoin('users', 'contact.contact_id', '=', 'users.id')
            ->orderBy('users.login')
            ->get();

        if (count($contacts) > 0) {
            echo '<label for="inputContact">Или выберите из списка</label>';
            echo '<select class="form-control" id="inputContact" name="contact">';

            echo '<option value="0">Список контактов</option>';

            foreach($contacts as $data) {
                echo '<option value="'.$data->getContact()->login.'">'.$data->getContact()->login.'</option>';
            }
            echo '</select><br>';
        }
?>


    <label for="markItUp">Сообщение:</label>
    <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required>{{ App::getInput('msg') }}</textarea>
<?php

        if (App::user('point') < Setting::get('privatprotect')) {
            echo 'Проверочный код:<br>';
            echo '<img src="/captcha" alt=""><br>';
            echo '<input name="provkod" size="6" maxlength="6"><br>';
        }

        echo '<button class="btn btn-primary">Отправить</button></form></div><br>';

        echo 'Введите логин или выберите пользователя из своего контакт-листа<br>';
    }
?>

    <i class="fa fa-arrow-circle-up"></i> <a href="/private">К письмам</a><br>
    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br>
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br>

@stop
