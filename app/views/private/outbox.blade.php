@extends('layout')

@section('title')
    Отправленные сообщения - @parent
@stop

@section('content')

    <h1>Отправленные сообщения</h1>

    <?php

    echo '<i class="fa fa-envelope"></i> <a href="/private">Входящие ('.$page['totalInbox'].')</a> / ';
    echo '<b>Отправленные ('.$page['total'].')</b> / ';
    echo '<a href="/private/trash">Корзина ('. $page['totalTrash'].')</a><hr />';

    if ($messages->isNotEmpty()) {

        echo '<form action="/private/delete?type=outbox&amp;page='.$page['current'].'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
        echo '<div class="form">';
        echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
        echo '</div>';

        foreach($messages as $data) {

            echo '<div class="b">';
            echo '<div class="img">'.user_avatars($data['recipient']).'</div>';
            echo '<b>'.profile($data['recipient']).'</b>  ('.date_fixed($data['created_at']).')<br />';
            echo user_title($data['recipient']).' '.user_online($data['recipient']).'</div>';

            echo '<div>'.App::bbCode($data['text']).'<br />';

            echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';
            echo '<a href="/private/send?user='.$data->getRecipient()->login.'">Написать еще</a> / ';
            echo '<a href="/private/history?user='.$data->getRecipient()->login.'">История</a></div>';
        }

        echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

        App::pagination($page);

        echo 'Всего писем: <b>'.$page['total'].'</b><br />';
        echo 'Объем ящика: <b>'.Setting::get('limitmail').'</b><br /><br />';

        echo '<i class="fa fa-times"></i> <a href="/private/clear?type=outbox&amp;token='.$_SESSION['token'].'">Очистить ящик</a><br />';
    } else {
        show_error('Отправленных писем еще нет!');
    }

?>
    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br />
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br />
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br />

@stop
