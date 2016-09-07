@extends('layout')

@section('title', 'Анкета пользователя '.e($uz).'. - @parent')

@section('content')

    <h1>{!! user_avatars($uz).nickname($uz) !!}<small>{{ user_visit($uz) }}</small></h1>

<?php
    if ($user['users_confirmreg'] == 1) {
    echo '<b><span style="color:#ff0000">Внимание, аккаунт требует подтверждение регистрации!</span></b><br />';
    }

    if ($user['users_ban'] == 1 && $user['users_timeban'] > SITETIME) {
    echo '<div class="form">';
        echo '<b><span style="color:#ff0000">Внимание, юзер находится в бане!</span></b><br />';
        echo 'До окончания бана осталось '.formattime($user['users_timeban'] - SITETIME).'<br />';
        echo 'Причина: '.bb_code($user['users_reasonban']).'</div>';
    }

    if ($user['users_level'] >= 101 && $user['users_level'] <= 105) {
    echo '<div class="info">Должность: <b>'.user_status($user['users_level']).'</b></div>';
    }

    if (!empty($user['users_picture']) && file_exists(BASEDIR.'/upload/photos/'.$user['users_picture'])) {
    echo '<a class="pull-right" href="/upload/photos/'.$user['users_picture'].'">';
        echo resize_image('upload/photos/', $user['users_picture'], $config['previewsize'], array('alt' => nickname($user['users_login']), 'class' => 'img-responsive img-rounded')).'</a>';
    } else {
    echo '<img src="/images/img/photo.jpg" alt="Фото" class="pull-right img-responsive img-rounded" />';
    }

    echo 'Cтатус: <b><a href="statusfaq.php">'.user_title($user['users_login']).'</a></b><br />';

    echo user_gender($user['users_login']).'Пол: ';
    echo ($user['users_gender'] == 1) ? 'Мужской <br />' : 'Женский<br />';

    echo 'Логин: <b>'.$user['users_login'].'</b><br />';
    if (!empty($user['users_nickname'])) {
    echo 'Ник: <b>'.$user['users_nickname'].'</b><br />';
    }
    if (!empty($user['users_name'])) {
    echo 'Имя: <b>'.$user['users_name'].'<br /></b>';
    }
    if (!empty($user['users_country'])) {
    echo 'Страна: <b>'.$user['users_country'].'<br /></b>';
    }
    if (!empty($user['users_city'])) {
    echo 'Откуда: '.$user['users_city'].'<br />';
    }
    if (!empty($user['users_birthday'])) {
    echo 'Дата рождения: '.$user['users_birthday'].'<br />';
    }
    if (!empty($user['users_icq'])) {
    echo 'ICQ: '.$user['users_icq'].' <br />';
    }
    if (!empty($user['users_skype'])) {
    echo 'Skype: '.$user['users_skype'].' <br />';
    }

    echo 'Всего посeщений: '.$user['users_visits'].'<br />';
    echo 'Сообщений на форуме: '.$user['users_allforum'].'<br />';
    echo 'Сообщений в гостевой: '.$user['users_allguest'].'<br />';
    echo 'Комментариев: '.$user['users_allcomments'].'<br />';
    echo 'Актив: '.points($user['users_point']).' <br />';
    echo 'Денег: '.moneys($user['users_money']).'<br />';

    if (!empty($user['users_themes'])) {
    echo 'Используемый скин: '.$user['users_themes'].'<br />';
    }
    echo 'Дата регистрации: '.date_fixed($user['users_joined'], 'j F Y').'<br />';

    $invite = DB::run() -> queryFetch("SELECT * FROM `invite` WHERE `invited`=?;", array($uz));
    if (!empty($invite)){
    echo 'Зарегистрирован по приглашению: '.profile($invite['user']).'<br />';
    }

    echo 'Последняя авторизация: '.date_fixed($user['users_timelastlogin']).'<br />';

    echo '<a href="banhist.php?uz='.$uz.'">Строгих нарушений: '.$user['users_totalban'].'</a><br />';

    echo '<a href="rathist.php?uz='.$uz.'">Авторитет: <b>'.format_num($user['users_rating']).'</b> (+'.$user['users_posrating'].'/-'.$user['users_negrating'].')</a><br />';

    if (is_user() && $log != $uz) {
    echo '[ <a href="rating.php?uz='.$uz.'&amp;vote=1"><img src="/images/img/plus.gif" alt="Плюс" /><span style="color:#0099cc"> Плюс</span></a> / ';
    echo '<a href="rating.php?uz='.$uz.'&amp;vote=0"><span style="color:#ff0000">Минус</span> <img src="/images/img/minus.gif" alt="Минус" /></a> ]<br />';
    }

    echo '<b><a href="/forum/active.php?act=themes&amp;uz='.$uz.'">Форум</a></b> (<a href="/forum/active.php?act=posts&amp;uz='.$uz.'">Сообщ.</a>) / ';
    echo '<b><a href="/load/active.php?act=files&amp;uz='.$uz.'">Загрузки</a></b> (<a href="/load/active.php?act=comments&amp;uz='.$uz.'">комм.</a>) / ';
    echo '<b><a href="/blog/active.php?act=blogs&amp;uz='.$uz.'">Блоги</a></b> (<a href="/blog/active.php?act=comments&amp;uz='.$uz.'">комм.</a>) / ';
    echo '<b><a href="/gallery/album.php?act=photo&amp;uz='.$uz.'">Галерея</a></b> (<a href="/gallery/comments.php?act=comments&amp;uz='.$uz.'">комм.</a>)<br />';

    if (!empty($user['users_info'])) {
    echo '<div class="hiding"><b>О себе</b>:<br />'.bb_code($user['users_info']).'</div>';
    }

    if (is_admin()) {
    $usernote = DB::run() -> queryFetch("SELECT * FROM `note` WHERE `note_user`=? LIMIT 1;", array($uz));

    echo '<div class="form">';
        echo '<img src="/images/img/pin.gif" alt="Заметка" /> <b>Заметка:</b> (<a href="/user/'.$uz.'/note">Изменить</a>)<br />';

        if (!empty($usernote['note_text'])) {
        echo bb_code($usernote['note_text']).'<br />';
        echo 'Изменено: '.profile($usernote['note_edit']).' ('.date_fixed($usernote['note_time']).')<br />';
        } else {
        echo'Записей еще нет!<br />';
        }

        echo '</div>';
    }

    echo '<div class="act">';
        echo '<img src="/images/img/wall.gif" alt="Стена" /> <a href="wall.php?uz='.$uz.'">Стена сообщений</a> ('.user_wall($uz).')<br />';

        if ($uz != $log) {
        echo '<img src="/images/img/users.gif" alt="Добавить" /> Добавить в ';
        echo '<a href="contact.php?act=add&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">контакт</a> / ';
        echo '<a href="ignore.php?act=add&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">игнор</a><br />';
        echo '<img src="/images/img/mail.gif" alt="Отправить" /> <a href="private.php?act=submit&amp;uz='.$uz.'">Отправить сообщение</a><br />';

        echo '<img src="/images/img/money.gif" alt="Перечислить" /> <a href="/pages/perevod.php?uz='.$uz.'">Перечислить денег</a><br />';

        if (!empty($user['users_site'])) {
        echo '<img src="/images/img/homepage.gif" alt="Перейти" /> <a href="'.$user['users_site'].'">Перейти на сайт '.$uz.'</a><br />';
        }

        if (is_admin(array(101, 102, 103))) {
        if (!empty($config['invite'])) {
        echo '<img src="/images/img/error.gif" alt="Бан" /> <a href="/admin/invitations.php?act=send&amp;user='.$uz.'&amp;uid='.$_SESSION['token'].'">Отправить инвайт</a><br />';
        }
        echo '<img src="/images/img/error.gif" alt="Бан" /> <a href="/admin/zaban.php?act=edit&amp;uz='.$uz.'">Бан / Разбан</a><br />';
        }

        if (is_admin(array(101, 102))) {
        echo '<img src="/images/img/panel.gif" alt="Редактировать" /> <a href="/admin/users.php?act=edit&amp;uz='.$uz.'">Редактировать</a><br />';
        }
        } else {
        echo '<img src="/images/img/user.gif" alt="Профиль" /> <a href="profile.php">Мой профиль</a><br />';
        echo '<img src="/images/img/account.gif" alt="Данные" /> <a href="account.php">Мои данные</a><br />';
        echo '<img src="/images/img/panel.gif" alt="Настройки" /> <a href="setting.php">Настройки</a><br />';
        }

        echo '</div>';
    ?>
@stop
