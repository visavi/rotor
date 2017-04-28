@extends('layout')

@section('title')
    Приватные сообщения - @parent
@stop

@section('content')

    <h1>Приватные сообщения</h1>

    <?php

    if ($newprivat > 0) {
        echo '<div style="text-align:center"><b><span style="color:#ff0000">Получено новых писем: '.App::user('newprivat').'</span></b></div>';
    }

    if ($page['total'] >= (App::setting('limitmail') - (App::setting('limitmail') / 10)) && $page['total'] < App::setting('limitmail')) {
        echo '<div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик почти заполнен, необходимо очистить или удалить старые сообщения!</span></b></div>';
    }

    if ($page['total'] >= App::setting('limitmail')) {
        echo '<div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик переполнен, вы не сможете получать письма пока не очистите его!</span></b></div>';
    }

    echo '<i class="fa fa-envelope"></i> <b>Входящие ('.$page['total'].')</b> / ';
    echo '<a href="/private/output">Отправленные ('.$page['totalOutbox'].')</a> / ';
    echo '<a href="/private/trash">Корзина ('.$page['totalTrash'].')</a><hr />';

    if ($messages->isNotEmpty()) {

    echo '<form action="/private?act=del&amp;page='.$page['current'].'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
        echo '<div class="form">';
            echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
            echo '</div>';

        foreach($messages as $data) {

        echo '<div class="b">';
            echo '<div class="img">'.user_avatars($data->author).'</div>';
            if ($data->author) {
            echo '<b>' . profile($data->author) . '</b>  (' . date_fixed($data['created_at']) . ')<br />';
            echo user_title($data->author) . ' ' . user_online($data->author);
            } else {
            echo '<b>Система</b>';
            }

            echo '</div>';
        echo '<div>'.App::bbCode($data['text']).'<br />';

            echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';

            if ($data->author) {

            echo '<a href="/private/send?user=' . $data->getAuthor()->login . '">Ответить</a> / ';
            echo '<a href="/private/history?user=' . $data->getAuthor()->login . '">История</a> / ';
            echo '<a href="/contact?act=add&amp;uz=' . $data->getAuthor()->login . '&amp;token=' . $_SESSION['token'] . '">В контакт</a> / ';
            echo '<a href="/ignore?act=add&amp;uz=' . $data->getAuthor()->login . '&amp;token=' . $_SESSION['token'] . '">Игнор</a> / ';

            echo '<noindex><a href="#" onclick="return sendComplaint(this)" data-type="/private" data-id="' . $data['id'] . '" data-token="' . $_SESSION['token'] . '" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a></noindex>';
            }

            echo '</div>';
        }

        echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

    App::pagination($page);

    echo 'Всего писем: <b>'.$page['total'].'</b><br />';
    echo 'Объем ящика: <b>'.App::setting('limitmail').'</b><br /><br />';

    echo '<i class="fa fa-times"></i> <a href="/private/alldel?token='.$_SESSION['token'].'">Очистить ящик</a><br />';
    } else {
    show_error('Входящих писем еще нет!');
    }
?>

    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br />
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br />
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br />

@stop
