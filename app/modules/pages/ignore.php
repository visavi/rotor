<?php
App::view($config['themes'].'/index');

if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}
if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

show_title('Игнор-лист');

if (is_user()) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `ignore` WHERE `ignore_user`=?;", array($log));

            if ($total > 0) {
                if ($start >= $total) {
                    $start = last_page($total, $config['ignorlist']);
                }

                $queryignor = DB::run() -> query("SELECT * FROM `ignore` WHERE `ignore_user`=? ORDER BY `ignore_time` DESC LIMIT ".$start.", ".$config['ignorlist'].";", array($log));

                echo '<form action="/ignore?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

                while ($data = $queryignor -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['ignore_name']).'</div>';

                    echo '<b>'.profile($data['ignore_name']).'</b> <small>('.date_fixed($data['ignore_time']).')</small><br />';
                    echo user_title($data['ignore_name']).' '.user_online($data['ignore_name']).'</div>';

                    echo '<div>';
                    if (!empty($data['ignore_text'])) {
                        echo 'Заметка: '.$data['ignore_text'].'<br />';
                    }

                    echo '<input type="checkbox" name="del[]" value="'.$data['ignore_id'].'" /> ';
                    echo '<a href="/private?act=submit&amp;uz='.$data['ignore_name'].'">Написать</a> | ';
                    echo '<a href="/ignore?act=note&amp;id='.$data['ignore_id'].'">Заметка</a>';
                    echo '</div>';
                }

                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                page_strnavigation('/ignore?', $config['ignorlist'], $start, $total);

                echo 'Всего в игноре: <b>'.(int)$total.'</b><br />';
            } else {
                show_error('Игнор-лист пуст!');
            }

            echo '<br /><div class="form"><form method="post" action="/ignore?act=add&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">';
            echo '<b>Логин юзера:</b><br /><input name="uz" />';
            echo '<input value="Добавить" type="submit" /></form></div><br />';
            break;

        ############################################################################################
        ##                                 Добавление пользователей                               ##
        ############################################################################################
        case 'add':

            $uid = check($_GET['uid']);
            if (isset($_POST['uz'])) {
                $uz = check($_POST['uz']);
            } elseif (isset($_GET['uz'])) {
                $uz = check($_GET['uz']);
            } else {
                $uz = "";
            }

            if ($uid == $_SESSION['token']) {
                if ($uz != $log) {
                    $queryuser = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
                    if (!empty($queryuser)) {

                        if ($queryuser['users_level']<101 || $queryuser['users_level']>105){

                            $total = DB::run() -> querySingle("SELECT count(*) FROM `ignore` WHERE `ignore_user`=?;", array($log));
                            if ($total <= $config['limitignore']) {
                                // ------------------------ Проверка на существование ------------------------//
                                if (!is_ignore($log, $uz)){

                                    DB::run() -> query("INSERT INTO `ignore` (`ignore_user`, `ignore_name`, `ignore_time`) VALUES (?, ?, ?);", array($log, $uz, SITETIME));
                                    // ----------------------------- Проверка на игнор ----------------------------//
                                    $ignorstr = DB::run() -> querySingle("SELECT `ignore_id` FROM `ignore` WHERE `ignore_user`=? AND `ignore_name`=? LIMIT 1;", array($uz, $log));
                                    if (empty($ignorstr)) {
                                        DB::run() -> query("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1 WHERE `users_login`=?", array($uz));
                                        // ------------------------------Уведомление по привату------------------------//
                                        $textpriv = '<img src="/assets/img/images/custom.gif" alt="custom" /> Пользователь [b]'.nickname($log).'[/b] добавил вас в свой игнор-лист!';
                                        DB::run() -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);", array($uz, $log, $textpriv, SITETIME));
                                    }

                                    $_SESSION['note'] = 'Пользователь успешно отправлен в игнор!';
                                    redirect("/ignore?start=$start");

                                } else {
                                    show_error('Ошибка! Данный пользователь уже есть в игнор-листе!');
                                }
                            } else {
                                show_error('Ошибка! В игнор-листе разрешено не более '.$config['limitignore'].' пользователей!');
                            }
                        } else {
                            show_error('Ошибка! Запрещено добавлять в игнор администрацию сайта!');
                        }
                    } else {
                        show_error('Ошибка! Данного адресата не существует!');
                    }
                } else {
                    show_error('Ошибка! Запрещено добавлять свой логин!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/ignore?start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Изменение заметки                                   ##
        ############################################################################################
        case 'note':

            if (isset($_GET['id'])) {
                $id = abs(intval($_GET['id']));
            } else {
                $id = 0;
            }

            if ($id > 0) {
                $data = DB::run() -> queryFetch("SELECT * FROM `ignore` WHERE `ignore_id`=? AND `ignore_user`=? LIMIT 1;", array($id, $log));

                if (!empty($data)) {
                    echo '<i class="fa fa-pencil"></i> Заметка для пользователя <b>'.nickname($data['ignore_name']).'</b> '.user_online($data['ignore_name']).':<br /><br />';

                    echo '<div class="form">';
                    echo '<form method="post" action="/ignore?act=editnote&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">';
                    echo 'Заметка:<br />';
                    echo '<textarea cols="25" rows="5" name="msg">'.$data['ignore_text'].'</textarea><br />';
                    echo '<input value="Редактировать" type="submit" /></form></div><br />';
                } else {
                    show_error('Ошибка редактирования заметки!');
                }
            } else {
                show_error('Ошибка! Не выбран пользователь для добавления заметки!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/ignore?start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Добавление заметки                                   ##
        ############################################################################################
        case 'editnote':
            $uid = check($_GET['uid']);
            $msg = check($_POST['msg']);
            if (isset($_GET['id'])) {
                $id = abs(intval($_GET['id']));
            } else {
                $id = 0;
            }

            if ($uid == $_SESSION['token']) {
                if ($id > 0) {
                    if (utf_strlen($msg) < 1000) {
                        DB::run() -> query("UPDATE `ignore` SET `ignore_text`=? WHERE `ignore_id`=? AND `ignore_user`=?;", array($msg, $id, $log));

                        $_SESSION['note'] = 'Заметка успешно отредактирована!';
                        redirect("/ignore?start=$start");

                    } else {
                        show_error('Ошибка! Слишком длинная заметка (не более 1000 символов)!');
                    }
                } else {
                    show_error('Ошибка! Не выбран пользователь для добавления заметки!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/ignore?act=note&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/ignore?start='.$start.'">К спискам</a><br />';
        break;

        ############################################################################################
        ##                                   Удаление пользователей                               ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if ($del > 0) {
                    $del = implode(',', $del);
                    DB::run() -> query("DELETE FROM `ignore` WHERE `ignore_id` IN (".$del.") AND `ignore_user`=?;", array($log));

                    $_SESSION['note'] = 'Выбранные пользователи успешно удалены из игнора!';
                    redirect("/ignore?start=$start");

                } else {
                    show_error('Ошибка! Не выбраны пользователи для удаления!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/ignore?start='.$start.'">Вернуться</a><br />';
        break;

    endswitch;

} else {
    show_login('Вы не авторизованы, для просмотра игнор-листа, необходимо');
}

echo '<img src="/assets/img/images/users.gif" alt="image" /> <a href="/contact">Контакт-лист</a><br />';
echo '<img src="/assets/img/images/mail.gif" alt="image" /> <a href="/private">Сообщения</a><br />';

App::view($config['themes'].'/foot');
