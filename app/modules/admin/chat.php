<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}

if (is_admin()) {
    show_title('Админ-чат');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<a href="/admin/chat?rand='.mt_rand(100, 999).'">Обновить</a> / ';
            echo '<a href="/smiles">Смайлы</a> / ';
            echo '<a href="/tags">Теги</a><hr />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `chat`;");

            if ($udata['users_newchat'] != stats_newchat()) {
                DB::run() -> query("UPDATE `users` SET `users_newchat`=? WHERE `users_login`=? LIMIT 1;", array(stats_newchat(), $log));
            }

            if ($total > 0) {
                if ($start >= $total) {
                    $start = last_page($total, $config['chatpost']);
                }

                $querychat = DB::run() -> query("SELECT * FROM `chat` ORDER BY `chat_time` DESC LIMIT ".$start.", ".$config['chatpost'].";");

                while ($data = $querychat -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['chat_user']).'</div>';

                    echo '<b>'.profile($data['chat_user']).'</b> <small>('.date_fixed($data['chat_time']).')</small><br />';
                    echo user_title($data['chat_user']).' '.user_online($data['chat_user']).'</div>';

                    if ($log != $data['chat_user']) {
                        echo '<div class="right">';
                        echo '<a href="/admin/chat?act=reply&amp;id='.$data['chat_id'].'&amp;start='.$start.'">Отв</a> / ';
                        echo '<a href="/admin/chat?act=quote&amp;id='.$data['chat_id'].'&amp;start='.$start.'">Цит</a></div>';
                    }

                    if ($log == $data['chat_user'] && $data['chat_time'] + 600 > SITETIME) {
                        echo '<div class="right"><a href="/admin/chat?act=edit&amp;id='.$data['chat_id'].'&amp;start='.$start.'">Редактировать</a></div>';
                    }

                    echo '<div>'.bb_code($data['chat_text']).'<br />';

                    if (!empty($data['chat_edit'])) {
                        echo '<i class="fa fa-exclamation-circle"></i> <small>Отредактировано: '.nickname($data['chat_edit']).' ('.date_fixed($data['chat_edit_time']).')</small><br />';
                    }

                    echo '<span class="data">('.$data['chat_brow'].', '.$data['chat_ip'].')</span>';

                    echo '</div>';
                }

                page_strnavigation('/admin/chat?', $config['chatpost'], $start, $total);
            } else {
                show_error('Сообщений нет, будь первым!');
            }

            echo '<div class="form">';
            echo '<form action="/admin/chat?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
            echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
            echo '<input type="submit" value="Написать" /></form></div><br />';

            if (is_admin(array(101)) && $total > 0) {
                echo '<i class="fa fa-times"></i> <a href="/admin/chat?act=prodel">Очистить чат</a><br />';
            }
        break;

        ############################################################################################
        ##                                   Добавление сообщений                                 ##
        ############################################################################################
        case 'add':

            $msg = check($_POST['msg']);
            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1500) {
                    $post = DB::run() -> queryFetch("SELECT * FROM `chat` ORDER BY `chat_id` DESC LIMIT 1;");

                    if ($log == $post['chat_user'] && $post['chat_time'] + 1800 > SITETIME && (utf_strlen($msg) + utf_strlen($post['chat_text']) <= 1500)) {

                        $newpost = $post['chat_text']."\n\n".'[i][size=1]Добавлено через '.maketime(SITETIME - $post['chat_time']).' сек.[/size][/i]'."\n".$msg;
                        DB::run() -> query("UPDATE `chat` SET `chat_text`=? WHERE `chat_id`=? LIMIT 1;", array($newpost, $post['chat_id']));

                    } else {

                        DB::run() -> query("INSERT INTO `chat` (`chat_user`, `chat_text`, `chat_ip`, `chat_brow`, `chat_time`) VALUES (?, ?, ?, ?, ?);", array($log, $msg, $ip, $brow, SITETIME));
                    }

                    DB::run() -> query("DELETE FROM `chat` WHERE `chat_time` < (SELECT MIN(`chat_time`) FROM (SELECT `chat_time` FROM `chat` ORDER BY `chat_time` DESC LIMIT ".$config['maxpostchat'].") AS del);");

                    DB::run() -> query("UPDATE `users` SET `users_newchat`=? WHERE `users_login`=? LIMIT 1;", array(stats_newchat(), $log));

                    $_SESSION['note'] = 'Сообщение успешно добавлено!';
                    redirect ("/admin/chat");

                } else {
                    show_error('Ошибка! Слишком длинное или короткое сообщение!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Ответ на сообщение                                   ##
        ############################################################################################
        case 'reply':

            $id = abs(intval($_GET['id']));

            echo '<b><big>Ответ на сообщение</big></b><br /><br />';

            $post = DB::run() -> queryFetch("SELECT * FROM `chat` WHERE `chat_id`=? LIMIT 1;", array($id));

            if (!empty($post)) {
                echo '<div class="b"><i class="fa fa-pencil"></i> <b>'.profile($post['chat_user']).'</b> '.user_online($post['chat_user']).' <small>('.date_fixed($post['chat_time']).')</small></div>';
                echo '<div>Сообщение: '.bb_code($post['chat_text']).'</div><hr />';

                echo '<div class="form">';
                echo '<form action="/admin/chat?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<textarea id="markItUp" cols="25" rows="5" name="msg" id="msg">[b]'.nickname($post['chat_user']).'[/b], </textarea><br />';
                echo '<input type="submit" value="Ответить" /></form></div><br />';
            } else {
                show_error('Ошибка! Выбранное вами сообщение для ответа не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat?start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Цитирование сообщения                                ##
        ############################################################################################
        case 'quote':

            $id = abs(intval($_GET['id']));

            echo '<b><big>Цитирование</big></b><br /><br />';

            $post = DB::run() -> queryFetch("SELECT * FROM `chat` WHERE `chat_id`=? LIMIT 1;", array($id));

            if (!empty($post)) {
                echo '<div class="form">';
                echo '<form action="/admin/chat?act=add&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<textarea id="markItUp" cols="25" rows="5" name="msg" id="msg">[quote][b]'.nickname($post['chat_user']).'[/b] ('.date_fixed($post['chat_time']).')'."\r\n".$post['chat_text'].'[/quote]'."\r\n".'</textarea><br />';
                echo '<input type="submit" value="Ответить" /></form></div><br />';
            } else {
                show_error('Ошибка! Выбранное вами сообщение для цитирования не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat?start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Подготовка к редактированию                          ##
        ############################################################################################
        case 'edit':

            $id = abs(intval($_GET['id']));

            $post = DB::run() -> queryFetch("SELECT * FROM `chat` WHERE `chat_id`=? AND `chat_user`=? LIMIT 1;", array($id, $log));

            if (!empty($post)) {
                if ($post['chat_time'] + 600 > SITETIME) {

                    echo '<i class="fa fa-pencil"></i> <b>'.nickname($post['chat_user']).'</b> <small>('.date_fixed($post['chat_time']).')</small><br /><br />';

                    echo '<div class="form">';
                    echo '<form action="/admin/chat?act=editpost&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo '<textarea id="markItUp" cols="25" rows="5" name="msg" id="msg">'.$post['chat_text'].'</textarea><br />';
                    echo '<input type="submit" value="Редактировать" /></form></div><br />';
                } else {
                    show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
                }
            } else {
                show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat?start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Редактирование сообщения                            ##
        ############################################################################################
        case 'editpost':

            $uid = check($_GET['uid']);
            $id = abs(intval($_GET['id']));
            $msg = check($_POST['msg']);

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1500) {
                    $post = DB::run() -> queryFetch("SELECT * FROM `chat` WHERE `chat_id`=? AND `chat_user`=? LIMIT 1;", array($id, $log));

                    if (!empty($post)) {
                        if ($post['chat_time'] + 600 > SITETIME) {

                            DB::run() -> query("UPDATE `chat` SET `chat_text`=?, `chat_edit`=?, `chat_edit_time`=? WHERE `chat_id`=? LIMIT 1;", array($msg, $log, SITETIME, $id));

                            $_SESSION['note'] = 'Сообщение успешно отредактировано!';
                            redirect ("/admin/chat?start=$start");

                        } else {
                            show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
                        }
                    } else {
                        show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинное или короткое сообщение!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat?act=edit&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/chat?start='.$start.'">В админ-чат</a><br />';
        break;

        ############################################################################################
        ##                                 Подтверждение очистки                                  ##
        ############################################################################################
        case 'prodel':
            echo 'Вы уверены что хотите удалить все сообщения в админ-чате?<br />';
            echo '<i class="fa fa-times"></i> <b><a href="/admin/chat?act=alldel&amp;uid='.$_SESSION['token'].'">Да, уверен!</a></b><br /><br />';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Очистка админ-чата                                   ##
        ############################################################################################
        case 'alldel':

            $uid = check($_GET['uid']);

            if (is_admin(array(101))) {
                if ($uid == $_SESSION['token']) {
                    DB::run() -> query("TRUNCATE `chat`;");

                    $_SESSION['note'] = 'Админ-чат успешно очищен!';
                    redirect ("/admin/chat");
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Очищать админ-чат могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect ('/');
}

App::view($config['themes'].'/foot');
