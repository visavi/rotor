@extends('layout')

@section('title')
    История переписки - @parent
@stop

@section('content')

    <h1>История переписки</h1>

    <?php

    echo '<i class="fa fa-envelope"></i> <a href="/private">Входящие</a> / ';
    echo '<a href="/private/outbox">Отправленные</a> / ';
    echo '<a href="/private/trash">Корзина</a><hr>';

    if ($messages->isNotEmpty()) {

        foreach($messages as $data) {
            echo '<div class="b">';
            echo user_avatars($data['author']);
            echo '<b>'.profile($data['author']).'</b> '.user_online($data['author']).' ('.date_fixed($data['created_at']).')</div>';
            echo '<div>'.App::bbCode($data['text']).'</div>';
        }

        App::pagination($page);

    } else {
        show_error('История переписки отсутствует!');
    }

    echo '<br><div class="form">';
    echo '<form action="/private/send?user='.$user->login.'" method="post">';
    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
?>

    <label for="markItUp">Сообщение:</label>
    <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required></textarea>

  <?php
    if (App::user('point') < Setting::get('privatprotect')) {
        echo 'Проверочный код:<br> ';
        echo '<img src="/captcha" alt=""><br>';
        echo '<input name="provkod" size="6" maxlength="6"><br>';
    }

    echo '<button class="btn btn-primary">Быстрый ответ</button></form></div><br>';

    echo 'Всего писем: <b>'.$page['total'].'</b><br><br>';

?>
    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br>
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br>

@stop
