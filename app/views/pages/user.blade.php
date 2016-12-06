@extends('layout')

@section('title', 'Анкета пользователя '.e($user['login']).' - @parent')

@section('content')

    <h1>{!! user_avatars($user['login']).nickname($user['login']) !!}<small>{{ user_visit($user['login']) }}</small></h1>

<?php
    if ($user['confirmreg'] == 1) {
    echo '<b><span style="color:#ff0000">Внимание, аккаунт требует подтверждение регистрации!</span></b><br />';
    }

    if ($user['ban'] == 1 && $user['timeban'] > SITETIME) {
    echo '<div class="form">';
        echo '<b><span style="color:#ff0000">Внимание, пользователь забанен!</span></b><br />';
        echo 'До окончания бана осталось '.formattime($user['timeban'] - SITETIME).'<br />';
        echo 'Причина: '.App::bbCode($user['reasonban']).'</div>';
    }

    if ($user['level'] >= 101 && $user['level'] <= 105) {
    echo '<div class="info">Должность: <b>'.user_status($user['level']).'</b></div>';
    }

    if (!empty($user['picture']) && file_exists(HOME.'/uploads/photos/'.$user['picture'])) {
    echo '<a class="pull-right" href="/uploads/photos/'.$user['picture'].'">';
        echo resize_image('uploads/photos/', $user['picture'], $config['previewsize'], ['alt' => nickname($user['login']), 'class' => 'img-responsive img-rounded']).'</a>';
    } else {
    echo '<img src="/assets/img/images/photo.jpg" alt="Фото" class="pull-right img-responsive img-rounded" />';
    }

    echo 'Cтатус: <b><a href="/statusfaq">'.user_title($user['login']).'</a></b><br />';

    echo user_gender($user['login']).'Пол: ';
    echo ($user['gender'] == 1) ? 'Мужской <br />' : 'Женский<br />';

    echo 'Логин: <b>'.$user['login'].'</b><br />';
    if (!empty($user['nickname'])) {
    echo 'Ник: <b>'.$user['nickname'].'</b><br />';
    }
    if (!empty($user['name'])) {
    echo 'Имя: <b>'.$user['name'].'<br /></b>';
    }
    if (!empty($user['country'])) {
    echo 'Страна: <b>'.$user['country'].'<br /></b>';
    }
    if (!empty($user['city'])) {
    echo 'Откуда: '.$user['city'].'<br />';
    }
    if (!empty($user['birthday'])) {
    echo 'Дата рождения: '.$user['birthday'].'<br />';
    }
    if (!empty($user['icq'])) {
    echo 'ICQ: '.$user['icq'].' <br />';
    }
    if (!empty($user['skype'])) {
    echo 'Skype: '.$user['skype'].' <br />';
    }

    echo 'Всего посeщений: '.$user['visits'].'<br />';
    echo 'Сообщений на форуме: '.$user['allforum'].'<br />';
    echo 'Сообщений в гостевой: '.$user['allguest'].'<br />';
    echo 'Комментариев: '.$user['allcomments'].'<br />';
    echo 'Актив: '.points($user['point']).' <br />';
    echo 'Денег: '.moneys($user['money']).'<br />';

    if (!empty($user['themes'])) {
    echo 'Используемый скин: '.$user['themes'].'<br />';
    }
    echo 'Дата регистрации: '.date_fixed($user['joined'], 'j F Y').'<br />';

    $invite = DB::run() -> queryFetch("SELECT * FROM `invite` WHERE `invited`=?;", [$user['login']]);
    if (!empty($invite)){
    echo 'Зарегистрирован по приглашению: '.profile($invite['user']).'<br />';
    }

    echo 'Последняя авторизация: '.date_fixed($user['timelastlogin']).'<br />';

    echo '<a href="/banhist?uz='.$user['login'].'">Строгих нарушений: '.$user['totalban'].'</a><br />';

    echo '<a href="/rathist?uz='.$user['login'].'">Авторитет: <b>'.format_num($user['rating']).'</b> (+'.$user['posrating'].'/-'.$user['negrating'].')</a><br />';

    if (is_user() && $log != $user['login']) {
    echo '[ <a href="/rating?uz='.$user['login'].'&amp;vote=1"><i class="fa fa-thumbs-up"></i><span style="color:#0099cc"> Плюс</span></a> / ';
    echo '<a href="/rating?uz='.$user['login'].'&amp;vote=0"><span style="color:#ff0000">Минус</span> <i class="fa fa-thumbs-down"></i></a> ]<br />';
    }

    echo '<b><a href="/forum/active/themes?user='.$user['login'].'">Форум</a></b> (<a href="/forum/active/posts?user='.$user['login'].'">Сообщ.</a>) / ';
    echo '<b><a href="/load/active?act=files&amp;uz='.$user['login'].'">Загрузки</a></b> (<a href="/load/active?act=comments&amp;uz='.$user['login'].'">комм.</a>) / ';
    echo '<b><a href="/blog/active?act=blogs&amp;uz='.$user['login'].'">Блоги</a></b> (<a href="/blog/active?act=comments&amp;uz='.$user['login'].'">комм.</a>) / ';
    echo '<b><a href="/gallery/album?act=photo&amp;uz='.$user['login'].'">Галерея</a></b> (<a href="/gallery/comments?act=comments&amp;uz='.$user['login'].'">комм.</a>)<br />';

    if (!empty($user['info'])) {
    echo '<div class="hiding"><b>О себе</b>:<br />'.App::bbCode($user['info']).'</div>';
    }

    if (is_admin()) {
    $usernote = DB::run() -> queryFetch("SELECT * FROM `note` WHERE `user`=? LIMIT 1;", [$user['login']]);

    echo '<div class="form">';
        echo '<i class="fa fa-thumb-tack"></i> <b>Заметка:</b> (<a href="/user/'.$user['login'].'/note">Изменить</a>)<br />';

        if (!empty($usernote['text'])) {
        echo App::bbCode($usernote['text']).'<br />';
        echo 'Изменено: '.profile($usernote['edit']).' ('.date_fixed($usernote['time']).')<br />';
        } else {
        echo'Записей еще нет!<br />';
        }

        echo '</div>';
    }

    echo '<div class="act">';
        echo '<i class="fa fa-sticky-note"></i> <a href="/wall?uz='.$user['login'].'">Стена сообщений</a> ('.user_wall($user['login']).')<br />';

        if ($user['login'] != $log) {
        echo '<i class="fa fa-address-book"></i> Добавить в ';
        echo '<a href="/contact?act=add&amp;uz='.$user['login'].'&amp;uid='.$_SESSION['token'].'">контакт</a> / ';
        echo '<a href="/ignore?act=add&amp;uz='.$user['login'].'&amp;uid='.$_SESSION['token'].'">игнор</a><br />';
        echo '<i class="fa fa-envelope"></i> <a href="/private?act=submit&amp;uz='.$user['login'].'">Отправить сообщение</a><br />';

        echo '<i class="fa fa-money"></i> <a href="/games/transfer?uz='.$user['login'].'">Перечислить денег</a><br />';

        if (!empty($user['site'])) {
        echo '<i class="fa fa-home"></i> <a href="'.$user['site'].'">Перейти на сайт '.$user['login'].'</a><br />';
        }

        if (is_admin([101, 102, 103])) {
        if (!empty($config['invite'])) {
        echo '<i class="fa fa-ban"></i> <a href="/admin/invitations?act=send&amp;user='.$user['login'].'&amp;uid='.$_SESSION['token'].'">Отправить инвайт</a><br />';
        }
        echo '<i class="fa fa-ban"></i> <a href="/admin/ban?act=edit&amp;uz='.$user['login'].'">Бан / Разбан</a><br />';
        }

        if (is_admin([101, 102])) {
        echo '<i class="fa fa-wrench"></i> <a href="/admin/users?act=edit&amp;uz='.$user['login'].'">Редактировать</a><br />';
        }
        } else {
        echo '<i class="fa fa-user-circle-o"></i> <a href="/profile">Мой профиль</a><br />';
        echo '<i class="fa fa-cog"></i> <a href="/account">Мои данные</a><br />';
        echo '<i class="fa fa-wrench"></i> <a href="/setting">Настройки</a><br />';
        }

        echo '</div>';
    ?>
@stop
