<?php
App::view(App::setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
$page = abs(intval(Request::input('page', 1)));

//show_title('Контакт-лист');

if (is_user()) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `contact` WHERE `user`=?;", [App::getUsername()]);
            $page = App::paginate(App::setting('contactlist'), $total);

            if ($total > 0) {

                $querycontact = DB::run() -> query("SELECT * FROM `contact` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".App::setting('contactlist').";", [App::getUsername()]);

                echo '<form action="/contact?act=del&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

                while ($data = $querycontact -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['name']).'</div>';

                    echo '<b>'.profile($data['name']).'</b> <small>('.date_fixed($data['time']).')</small><br />';
                    echo user_title($data['name']).' '.user_online($data['name']).'</div>';

                    echo '<div>';
                    if (!empty($data['text'])) {
                        echo 'Заметка: '.$data['text'].'<br />';
                    }

                    echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';
                    echo '<a href="/private?act=submit&amp;uz='.$data['name'].'">Написать</a> | ';
                    echo '<a href="/games/transfer?uz='.$data['name'].'">Перевод</a> | ';
                    echo '<a href="/contact?act=note&amp;id='.$data['id'].'">Заметка</a>';
                    echo '</div>';
                }

                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                App::pagination($page);

                echo 'Всего в контактах: <b>'.(int)$total.'</b><br />';
            } else {
                show_error('Контакт-лист пуст!');
            }

            echo '<br /><div class="form"><form method="post" action="/contact?act=add&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'">';
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
                if ($uz != App::getUsername()) {
                    $queryuser = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
                    if (!empty($queryuser)) {

                        $total = DB::run() -> querySingle("SELECT count(*) FROM `contact` WHERE `user`=?;", [App::getUsername()]);
                        if ($total <= App::setting('limitcontact')) {
                            // ------------------------ Проверка на существование ------------------------//
                            if (!is_contact(App::getUsername(), $uz)){

                                DB::run() -> query("INSERT INTO `contact` (`user`, `name`, `time`) VALUES (?, ?, ?);", [App::getUsername(), $uz, SITETIME]);
                                // ----------------------------- Проверка на игнор ----------------------------//
                                $ignorstr = DB::run() -> querySingle("SELECT `id` FROM ignoring WHERE `user`=? AND `name`=? LIMIT 1;", [$uz, App::getUsername()]);
                                if (empty($ignorstr)) {
                                    DB::run() -> query("UPDATE `users` SET `newprivat`=`newprivat`+1 WHERE `login`=?", [$uz]);
                                    // ------------------------------Уведомление по привату------------------------//
                                    $textpriv = 'Пользователь [b]'.App::getUsername().'[/b] добавил вас в свой контакт-лист!';
                                    DB::run() -> query("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", [$uz, App::getUsername(), $textpriv, SITETIME]);
                                }

                                notice('Пользователь успешно добавлен в контакты!');
                                redirect("/contact?page=$page");

                            } else {
                                show_error('Ошибка! Данный пользователь уже есть в контакт-листе!');
                            }
                        } else {
                            show_error('Ошибка! В контакт-листе разрешено не более '.App::setting('limitcontact').' пользователей!');
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

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/contact?page='.$page.'">Вернуться</a><br />';
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
                $data = DB::run() -> queryFetch("SELECT * FROM contact WHERE id=? AND user=? LIMIT 1;", [$id, App::getUsername()]);

                if (!empty($data)) {
                    echo '<i class="fa fa-pencil"></i> Заметка для пользователя <b>'.$data['name'].'</b> '.user_online($data['name']).':<br /><br />';

                    echo '<div class="form">';
                    echo '<form method="post" action="/contact?act=editnote&amp;id='.$id.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'">';
                    echo 'Заметка:<br />';
                    echo '<textarea cols="25" rows="5" name="msg">'.$data['text'].'</textarea><br />';
                    echo '<input value="Редактировать" name="do" type="submit" /></form></div><br />';
                } else {
                    show_error('Ошибка редактирования заметки!');
                }
            } else {
                show_error('Ошибка! Не выбран пользователь для добавления заметки!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/contact?page='.$page.'">Вернуться</a><br />';
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
                        DB::run() -> query("UPDATE contact SET text=? WHERE id=? AND user=?;", [$msg, $id, App::getUsername()]);

                        notice('Заметка успешно отредактирована!');
                        redirect("/contact?page=$page");

                    } else {
                        show_error('Ошибка! Слишком длинная заметка (не более 1000 символов)!');
                    }
                } else {
                    show_error('Ошибка! Не выбран пользователь для добавления заметки!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/contact?act=note&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/contact?page='.$page.'">К спискам</a><br />';
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
                    DB::run() -> query("DELETE FROM contact WHERE id IN (".$del.") AND user=?;", [App::getUsername()]);

                    notice('Выбранные пользователи успешно удалены из контактов!');
                    redirect("/contact?page=$page");

                } else {
                    show_error('Ошибка! Не выбраны пользователи для удаления!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/contact?page='.$page.'">Вернуться</a><br />';
        break;

    endswitch;

} else {
    show_login('Вы не авторизованы, для просмотра контакт-листа, необходимо');
}

echo '<i class="fa fa-ban"></i> <a href="/ignore">Игнор-лист</a><br />';
echo '<i class="fa fa-envelope"></i> <a href="/private">Сообщения</a><br />';

App::view(App::setting('themes').'/foot');
