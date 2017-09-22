<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
$page = abs(intval(Request::input('page', 1)));

if (isAdmin()) {
    //show_title('Админ-чат');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<a href="/admin/chat?rand='.mt_rand(100, 999).'">Обновить</a> / ';
            echo '<a href="/smiles">Смайлы</a> / ';
            echo '<a href="/tags">Теги</a><hr>';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `chat`;");
            $page = paginate(setting('chatpost'), $total);

            if (user('newchat') != statsNewChat()) {
                DB::update("UPDATE `users` SET `newchat`=? WHERE `login`=? LIMIT 1;", [statsNewChat(), user('login')]);
            }

            if ($total > 0) {

                $querychat = DB::select("SELECT * FROM `chat` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".setting('chatpost').";");

                while ($data = $querychat -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.userAvatar($data['user']).'</div>';

                    echo '<b>'.profile($data['user']).'</b> <small>('.dateFixed($data['time']).')</small><br>';
                    echo userStatus($data['user']).' '.userOnline($data['user']).'</div>';

                    if (user('login') != $data['user']) {
                        echo '<div class="right">';
                        echo '<a href="/admin/chat?act=reply&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Отв</a> / ';
                        echo '<a href="/admin/chat?act=quote&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Цит</a></div>';
                    }

                    if (user('login') == $data['user'] && $data['time'] + 600 > SITETIME) {
                        echo '<div class="right"><a href="/admin/chat?act=edit&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a></div>';
                    }

                    echo '<div>'.bbCode($data['text']).'<br>';

                    if (!empty($data['edit'])) {
                        echo '<i class="fa fa-exclamation-circle"></i> <small>Отредактировано: '.$data['edit'].' ('.dateFixed($data['edit_time']).')</small><br>';
                    }

                    echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';

                    echo '</div>';
                }

                pagination($page);
            } else {
                showError('Сообщений нет, будь первым!');
            }

            echo '<div class="form">';
            echo '<form action="/admin/chat?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
            echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br>';
            echo '<input type="submit" value="Написать"></form></div><br>';

            if (isAdmin([101]) && $total > 0) {
                echo '<i class="fa fa-times"></i> <a href="/admin/chat?act=prodel">Очистить чат</a><br>';
            }
        break;

        ############################################################################################
        ##                                   Добавление сообщений                                 ##
        ############################################################################################
        case 'add':

            $msg = check($_POST['msg']);
            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (utfStrlen($msg) >= 5 && utfStrlen($msg) < 1500) {
                    $post = DB::run() -> queryFetch("SELECT * FROM `chat` ORDER BY `id` DESC LIMIT 1;");

                    if (user('login') == $post['user'] && $post['time'] + 1800 > SITETIME && (utfStrlen($msg) + utfStrlen($post['text']) <= 1500)) {

                        $newpost = $post['text']."\n\n".'[i][size=1]Добавлено через '.makeTime(SITETIME - $post['time']).' сек.[/size][/i]'."\n".$msg;
                        DB::update("UPDATE `chat` SET `text`=? WHERE `id`=? LIMIT 1;", [$newpost, $post['id']]);

                    } else {

                        DB::insert("INSERT INTO `chat` (`user`, `text`, `ip`, `brow`, `time`) VALUES (?, ?, ?, ?, ?);", [user('login'), $msg, getClientIp(), getUserAgent(), SITETIME]);
                    }

                    DB::update("UPDATE `users` SET `newchat`=? WHERE `login`=? LIMIT 1;", [statsNewChat(), user('login')]);

                    setFlash('success', 'Сообщение успешно добавлено!');
                    redirect ("/admin/chat");

                } else {
                    showError('Ошибка! Слишком длинное или короткое сообщение!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Ответ на сообщение                                   ##
        ############################################################################################
        case 'reply':

            $id = abs(intval($_GET['id']));

            echo '<b><big>Ответ на сообщение</big></b><br><br>';

            $post = DB::run() -> queryFetch("SELECT * FROM `chat` WHERE `id`=? LIMIT 1;", [$id]);

            if (!empty($post)) {
                echo '<div class="b"><i class="fa fa-pencil"></i> <b>'.profile($post['user']).'</b> '.userOnline($post['user']).' <small>('.dateFixed($post['time']).')</small></div>';
                echo '<div>Сообщение: '.bbCode($post['text']).'</div><hr>';

                echo '<div class="form">';
                echo '<form action="/admin/chat?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<textarea id="markItUp" cols="25" rows="5" name="msg" id="msg">[b]'.$post['user'].'[/b], </textarea><br>';
                echo '<input type="submit" value="Ответить"></form></div><br>';
            } else {
                showError('Ошибка! Выбранное вами сообщение для ответа не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat?page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Цитирование сообщения                                ##
        ############################################################################################
        case 'quote':

            $id = abs(intval($_GET['id']));

            echo '<b><big>Цитирование</big></b><br><br>';

            $post = DB::run() -> queryFetch("SELECT * FROM `chat` WHERE `id`=? LIMIT 1;", [$id]);

            if (!empty($post)) {
                echo '<div class="form">';
                echo '<form action="/admin/chat?act=add&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<textarea id="markItUp" cols="25" rows="5" name="msg" id="msg">[quote][b]'.$post['user'].'[/b] ('.dateFixed($post['time']).')'."\r\n".$post['text'].'[/quote]'."\r\n".'</textarea><br>';
                echo '<input type="submit" value="Ответить"></form></div><br>';
            } else {
                showError('Ошибка! Выбранное вами сообщение для цитирования не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat?page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Подготовка к редактированию                          ##
        ############################################################################################
        case 'edit':

            $id = abs(intval($_GET['id']));

            $post = DB::run() -> queryFetch("SELECT * FROM `chat` WHERE `id`=? AND `user`=? LIMIT 1;", [$id, user('login')]);

            if (!empty($post)) {
                if ($post['time'] + 600 > SITETIME) {

                    echo '<i class="fa fa-pencil"></i> <b>'.$post['user'].'</b> <small>('.dateFixed($post['time']).')</small><br><br>';

                    echo '<div class="form">';
                    echo '<form action="/admin/chat?act=editpost&amp;id='.$id.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo '<textarea id="markItUp" cols="25" rows="5" name="msg" id="msg">'.$post['text'].'</textarea><br>';
                    echo '<input type="submit" value="Редактировать"></form></div><br>';
                } else {
                    showError('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
                }
            } else {
                showError('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat?page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Редактирование сообщения                            ##
        ############################################################################################
        case 'editpost':

            $uid = check($_GET['uid']);
            $id = abs(intval($_GET['id']));
            $msg = check($_POST['msg']);

            if ($uid == $_SESSION['token']) {
                if (utfStrlen($msg) >= 5 && utfStrlen($msg) < 1500) {
                    $post = DB::run() -> queryFetch("SELECT * FROM `chat` WHERE `id`=? AND `user`=? LIMIT 1;", [$id, user('login')]);

                    if (!empty($post)) {
                        if ($post['time'] + 600 > SITETIME) {

                            DB::update("UPDATE `chat` SET `text`=?, `edit`=?, `edit_time`=? WHERE `id`=? LIMIT 1;", [$msg, user('login'), SITETIME, $id]);

                            setFlash('success', 'Сообщение успешно отредактировано!');
                            redirect ("/admin/chat?page=$page");

                        } else {
                            showError('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
                        }
                    } else {
                        showError('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
                    }
                } else {
                    showError('Ошибка! Слишком длинное или короткое сообщение!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat?act=edit&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/chat?page='.$page.'">В админ-чат</a><br>';
        break;

        ############################################################################################
        ##                                 Подтверждение очистки                                  ##
        ############################################################################################
        case 'prodel':
            echo 'Вы уверены что хотите удалить все сообщения в админ-чате?<br>';
            echo '<i class="fa fa-times"></i> <b><a href="/admin/chat?act=alldel&amp;uid='.$_SESSION['token'].'">Да, уверен!</a></b><br><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Очистка админ-чата                                   ##
        ############################################################################################
        case 'alldel':

            $uid = check($_GET['uid']);

            if (isAdmin([101])) {
                if ($uid == $_SESSION['token']) {
                    DB::run() -> query("TRUNCATE `chat`;");

                    setFlash('success', 'Админ-чат успешно очищен!');
                    redirect ("/admin/chat");
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Очищать админ-чат могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect ('/');
}

view(setting('themes').'/foot');
