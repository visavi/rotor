@extends('layout')

@section('title')
    Корзина - @parent
@stop

@section('content')

    <h1>Корзина</h1>

    <?php

    echo '<i class="fa fa-envelope"></i> <a href="/private">Входящие ('.$page['totalInbox'].')</a> / ';
    echo '<a href="/private/outbox">Отправленные ('.$page['totalOutbox'].')</a> / ';

    echo '<b>Корзина ('.$page['total'].')</b><hr />';

    if ($messages->isNotEmpty()) {

        foreach($messages as $data) {

            echo '<div class="b">';
            echo '<div class="img">'.user_avatars($data['author']).'</div>';
            echo '<b>'.profile($data['author']).'</b>  ('.date_fixed($data['time']).')<br />';
            echo user_title($data['author']).' '.user_online($data['author']).'</div>';

            echo '<div>'.App::bbCode($data['text']).'<br />';

            echo '<a href="/private/send?user='.$data->getAuthor()->login.'">Ответить</a> / ';
            echo '<a href="/contact?act=add&amp;uz='.$data->getAuthor()->login.'&amp;token='.$_SESSION['token'].'">В контакт</a> / ';
            echo '<a href="/ignore?act=add&amp;uz='.$data->getAuthor()->login.'&amp;token='.$_SESSION['token'].'">Игнор</a></div>';
        }

        App::pagination($page);

        echo 'Всего писем: <b>'.$page['total'].'</b><br />';
        echo 'Срок хранения (дней): <b>'.App::setting('expiresmail').'</b><br /><br />';

        echo '<i class="fa fa-times"></i> <a href="/private/clear?type=trash&amp;token='.$_SESSION['token'].'">Очистить ящик</a><br />';
    } else {
        show_error('Удаленных писем еще нет!');
    }

?>
    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br />
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br />
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br />

@stop
