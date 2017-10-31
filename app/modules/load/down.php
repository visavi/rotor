<?php
view(setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$cid = (isset($_GET['cid'])) ? abs(intval($_GET['cid'])) : 0;
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$sort = (isset($_GET['sort'])) ? check($_GET['sort']) : 'date';
$page = abs(intval(Request::input('page', 1)));

//show_title('Загрузки');

switch ($action):


############################################################################################
##                                     Скачивание файла                                   ##
############################################################################################
case 'load':

    $protect = check(Request::input('protect'));

    if (getUser() || $protect == $_SESSION['protect']) {

        $downs = DB::run() -> queryFetch("SELECT downs.*, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE downs.`id`=? LIMIT 1;", [$id]);

        if (!empty($downs)) {
            if (!empty($downs['active'])) {

                $folder = $downs['folder'] ? $downs['folder'].'/' : '';

                if (file_exists('uploads/files/'.$folder.$downs['link'])) {
                    $queryloads = DB::run() -> querySingle("SELECT ip FROM loads WHERE down=? AND ip=? LIMIT 1;", [$id, getIp()]);
                    if (empty($queryloads)) {
                        $expiresloads = SITETIME + 3600 * setting('expiresloads');

                        DB::delete("DELETE FROM loads WHERE time<?;", [SITETIME]);
                        DB::insert("INSERT INTO loads (down, ip, time) VALUES (?, ?, ?);", [$id, getIp(), $expiresloads]);
                        DB::update("UPDATE downs SET loads=loads+1, last_load=? WHERE id=?", [SITETIME, $id]);
                    }

                    redirect("/uploads/files/".$folder.$downs['link']);
                } else {
                    showError('Ошибка! Файла для скачивания не существует!');
                }
            } else {
                showError('Ошибка! Данный файл еще не проверен модератором!');
            }
        } else {
            showError('Ошибка! Данного файла не существует!');
        }
    } else {
        showError('Ошибка! Проверочное число не совпало с данными на картинке!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

############################################################################################
##                                       Оценка файла                                     ##
############################################################################################
case 'vote':

    $uid = check($_GET['uid']);
    if (isset($_POST['score'])) {
        $score = abs(intval($_POST['score']));
    } else {
        $score = 0;
    }

    if (getUser()) {
        if ($uid == $_SESSION['token']) {
            if ($score > 0 && $score <= 5) {
                $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);

                if (!empty($downs)) {
                    if (!empty($downs['active'])) {
                        if (getUser('login') != $downs['user']) {
                            $queryrated = DB::run() -> querySingle("SELECT `id` FROM `pollings` WHERE relate_type=? AND `relate_id`=? AND `user`=? LIMIT 1;", ['down', $id, getUser('login')]);

                            if (empty($queryrated)) {
                                $expiresrated = SITETIME + 3600 * setting('expiresrated');

                                DB::delete("DELETE FROM `pollings` WHERE relate_type=? AND `time`<?;", ['down', SITETIME]);
                                DB::insert("INSERT INTO `pollings` (relate_type, `relate_id`, `user`, `time`) VALUES (?, ?, ?, ?);", ['down', $id, getUser('login'), $expiresrated]);
                                DB::update("UPDATE `downs` SET `rating`=`rating`+?, `rated`=`rated`+1 WHERE `id`=?", [$score, $id]);

                                echo '<b>Спасибо! Ваша оценка "'.$score.'" принята!</b><br>';
                                echo 'Всего оценивало: '.($downs['rated'] + 1).'<br>';
                                echo 'Средняя оценка: '.round(($downs['rating'] + $score) / ($downs['rated'] + 1), 1).'<br><br>';
                            } else {
                                showError('Ошибка! Вы уже оценивали данный файл!');
                            }
                        } else {
                            showError('Ошибка! Нельзя голосовать за свой файл!');
                        }
                    } else {
                        showError('Ошибка! Данный файл еще не проверен модератором!');
                    }
                } else {
                    showError('Ошибка! Данного файла не существует!');
                }
            } else {
                showError('Ошибка! Необходимо поставить оценку от 1 до 5 включительно!');
            }
        } else {
            showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        showError('Вы не авторизованы, для голосования за файлы, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

############################################################################################
##                                        Комментарии                                     ##
############################################################################################
case 'comments':

    $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);

    if (!empty($downs)) {
        if (!empty($downs['active'])) {
            //setting('newtitle') = 'Комментарии - '.$downs['title'];

            echo '<i class="fa fa-file-o"></i> <b><a href="/load/down?act=view&amp;id='.$id.'">'.$downs['title'].'</a></b><br><br>';

            echo '<a href="/load/down?act=comments&amp;id='.$id.'&amp;rand='.mt_rand(100, 999).'">Обновить</a> / <a href="/load/rss?id='.$id.'">RSS-лента</a><hr>';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=? AND `relate_id`=?;", ['down', $id]);
            $page = paginate(setting('downcomm'), $total);

            if ($total > 0) {


                $is_admin = isAdmin();
                if ($is_admin) {
                    echo '<form action="/load/down?act=del&amp;id='.$id.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';
                }

                $querycomm = DB::select("SELECT * FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` ASC LIMIT ".$page['offset'].", ".setting('downcomm').";", ['down', $id]);

                while ($data = $querycomm -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.userAvatar($data['user']).'</div>';

                    if ($is_admin) {
                        echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['id'].'"></span>';
                    }

                    echo '<b>'.profile($data['user']).'</b> <small>('.dateFixed($data['time']).')</small><br>';
                    echo userStatus($data['user']).' '.userOnline($data['user']).'</div>';

                    if (!empty(getUser('login')) && getUser('login') != $data['user']) {
                        echo '<div class="right">';
                        echo '<a href="/load/down?act=reply&amp;id='.$id.'&amp;pid='.$data['id'].'&amp;page='.$page['current'].'">Отв</a> / ';
                        echo '<a href="/load/down?act=quote&amp;id='.$id.'&amp;pid='.$data['id'].'&amp;page='.$page['current'].'">Цит</a> / ';
                        echo '<a href="/load/down?act=spam&amp;id='.$id.'&amp;pid='.$data['id'].'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете факт спама?\')" rel="nofollow">Спам</a></div>';
                    }

                    if (getUser('login') == $data['user'] && $data['time'] + 600 > SITETIME) {
                        echo '<div class="right"><a href="/load/down?act=edit&amp;id='.$id.'&amp;pid='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a></div>';
                    }

                    echo '<div class="message">'.bbCode($data['text']).'<br>';

                    if (isAdmin()) {
                        echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';
                    }
                    echo '</div>';
                }

                if ($is_admin) {
                    echo '<span class="imgright"><input type="submit" value="Удалить выбранное"></span></form>';
                }

                pagination($page);
            } else {
                showError('Комментариев еще нет!');
            }

            if (getUser()) {
                echo '<div class="form">';
                echo '<form action="/load/down?act=add&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<b>Сообщение:</b><br>';
                echo '<textarea cols="25" rows="5" name="msg"></textarea><br>';
                echo '<input type="submit" value="Написать"></form></div><br>';

                echo '<a href="/rules">Правила</a> / ';
                echo '<a href="/smiles">Смайлы</a> / ';
                echo '<a href="/tags">Теги</a><br><br>';
            } else {
                showError('Вы не авторизованы, чтобы добавить сообщение, необходимо');
            }
        } else {
            showError('Ошибка! Данный файл еще не проверен модератором!');
        }
    } else {
        showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

############################################################################################
##                                Добавление комментариев                                 ##
############################################################################################
case 'add':

    $uid = check($_GET['uid']);
    $msg = check($_POST['msg']);

    if (getUser()) {
        if ($uid == $_SESSION['token']) {
            if (utfStrlen($msg) >= 5 && utfStrlen($msg) < 1000) {

                $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);

                if (!empty($downs)) {
                    if (!empty($downs['active'])) {
                        if (Flood::isFlood()) {

                            $msg = antimat($msg);

                            DB::insert("INSERT INTO `comments` (relate_type, `relate_category_id`, `relate_id`, `text`, `user`, `time`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", ['down',$downs['category_id'], $id, $msg, getUser('login'), SITETIME, getIp(), getBrowser()]);

                            DB::update("UPDATE `downs` SET `comments`=`comments`+1 WHERE `id`=?;", [$id]);
                            DB::update("UPDATE `users` SET `allcomments`=`allcomments`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [getUser('login')]);

                            setFlash('success', 'Сообщение успешно добавлено!');
                            redirect("/load/down?act=end&id=$id");
                        } else {
                            showError('Антифлуд! Разрешается отправлять сообщения раз в '.Flood::getPeriod().' секунд!');
                        }
                    } else {
                        showError('Ошибка! Данный файл еще не проверен модератором!');
                    }
                } else {
                    showError('Ошибка! Данного файла не существует!');
                }
            } else {
                showError('Ошибка! Слишком длинное или короткое сообщение!');
            }
        } else {
            showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        showError('Вы не авторизованы, чтобы добавить сообщение, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'">Вернуться</a><br>';
break;

############################################################################################
##                                    Жалоба на спам                                      ##
############################################################################################
case 'spam':

    $uid = check($_GET['uid']);
    $pid = abs(intval($_GET['pid']));

    if (getUser()) {
        if ($uid == $_SESSION['token']) {
            $data = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['down', $pid]);

            if (!empty($data)) {
                $queryspam = DB::run() -> querySingle("SELECT `id` FROM `spam` WHERE relate=? AND `idnum`=? LIMIT 1;", [5, $pid]);

                if (empty($queryspam)) {
                    if (Flood::isFlood()) {
                        DB::insert("INSERT INTO `spam` (relate, `idnum`, `user`, `login`, `text`, `time`, `addtime`, `link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", [5, $data['id'], getUser('login'), $data['user'], $data['text'], $data['time'], SITETIME, siteUrl().'/load/down?act=comments&amp;id='.$id.'&amp;page='.$page]);

                        setFlash('success', 'Жалоба успешно отправлена!');
                        redirect("/load/down?act=comments&id=$id&page=$page");
                    } else {
                        showError('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.Flood::getPeriod().' секунд!');
                    }
                } else {
                    showError('Ошибка! Жалоба на данное сообщение уже отправлена!');
                }
            } else {
                showError('Ошибка! Выбранное вами сообщение для жалобы не существует!');
            }
        } else {
            showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        showError('Вы не авторизованы, чтобы подать жалобу, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
break;

############################################################################################
##                                   Ответ на сообщение                                   ##
############################################################################################
case 'reply':

    $pid = abs(intval($_GET['pid']));

    echo '<b><big>Ответ на сообщение</big></b><br><br>';

    if (getUser()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['down', $pid]);

        if (!empty($post)) {
            echo '<div class="b"><i class="fa fa-pencil"></i> <b>'.profile($post['user']).'</b> '.userStatus($post['user']).' '.userOnline($post['user']).' <small>('.dateFixed($post['time']).')</small></div>';
            echo '<div>Сообщение: '.bbCode($post['text']).'</div><hr>';

            echo '<div class="form">';
            echo '<form action="/load/down?act=add&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
            echo 'Сообщение:<br>';
            echo '<textarea cols="25" rows="5" name="msg" id="msg">[b]'.$post['user'].'[/b], </textarea><br>';
            echo '<input type="submit" value="Ответить"></form></div><br>';
        } else {
            showError('Ошибка! Выбранное вами сообщение для ответа не существует!');
        }
    } else {
        showError('Вы не авторизованы, чтобы отвечать на сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
break;

############################################################################################
##                                   Цитирование сообщения                                ##
############################################################################################
case 'quote':

    $pid = abs(intval($_GET['pid']));

    echo '<b><big>Цитирование</big></b><br><br>';
    if (getUser()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['down', $pid]);

        if (!empty($post)) {
            echo '<div class="form">';
            echo '<form action="/load/down?act=add&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
            echo 'Сообщение:<br>';
            echo '<textarea cols="25" rows="5" name="msg" id="msg">[quote][b]'.$post['user'].'[/b] ('.dateFixed($post['time']).')'."\r\n".$post['text'].'[/quote]'."\r\n".'</textarea><br>';
            echo '<input type="submit" value="Цитировать"></form></div><br>';
        } else {
            showError('Ошибка! Выбранное вами сообщение для цитирования не существует!');
        }
    } else {
        showError('Вы не авторизованы, чтобы цитировать сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
break;

############################################################################################
##                                Подготовка к редактированию                             ##
############################################################################################
case 'edit':

    //setting('newtitle') = 'Редактирование сообщения';

    $pid = abs(intval($_GET['pid']));

    if (getUser()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? AND `user`=? LIMIT 1;", ['down', $pid, getUser('login')]);

        if (!empty($post)) {
            if ($post['time'] + 600 > SITETIME) {

                echo '<i class="fa fa-pencil"></i> <b>'.$post['user'].'</b> <small>('.dateFixed($post['time']).')</small><br><br>';

                echo '<div class="form">';
                echo '<form action="/load/down?act=editpost&amp;id='.$post['relate_id'].'&amp;pid='.$pid.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Редактирование сообщения:<br>';
                echo '<textarea cols="25" rows="5" name="msg" id="msg">'.$post['text'].'</textarea><br>';
                echo '<input type="submit" value="Редактировать"></form></div><br>';
            } else {
                showError('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
            }
        } else {
            showError('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
        }
    } else {
        showError('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
break;

############################################################################################
##                                    Редактирование сообщения                            ##
############################################################################################
case 'editpost':

    $uid = check($_GET['uid']);
    $pid = abs(intval($_GET['pid']));
    $msg = check($_POST['msg']);

    if (getUser()) {
        if ($uid == $_SESSION['token']) {
            if (utfStrlen($msg) >= 5 && utfStrlen($msg) < 1000) {
                $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? AND `user`=? LIMIT 1;", ['down', $pid, getUser('login')]);

                if (!empty($post)) {
                    if ($post['time'] + 600 > SITETIME) {

                        $msg = antimat($msg);

                        DB::update("UPDATE `comments` SET `text`=? WHERE relate_type=? AND `id`=?", [$msg, 'down', $pid]);

                        setFlash('success', 'Сообщение успешно отредактировано!');
                        redirect("/load/down?act=comments&id=$id&page=$page");
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
    } else {
        showError('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=edit&amp;id='.$id.'&amp;pid='.$pid.'&amp;page='.$page.'">Вернуться</a><br>';
break;

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'del':

    $uid = check($_GET['uid']);
    if (isset($_POST['del'])) {
        $del = intar($_POST['del']);
    } else {
        $del = 0;
    }

    if (isAdmin()) {
        if ($uid == $_SESSION['token']) {
            if (!empty($del)) {
                $del = implode(',', $del);

                $delcomments = DB::run() -> exec("DELETE FROM `comments` WHERE relate_type='down' AND `id` IN (".$del.") AND `relate_id`=".$id.";");
                DB::update("UPDATE `downs` SET `comments`=`comments`-? WHERE `id`=?;", [$delcomments, $id]);

                setFlash('success', 'Выбранные комментарии успешно удалены!');
                redirect("/load/down?act=comments&id=$id&page=$page");
            } else {
                showError('Ошибка! Отстутствуют выбранные комментарии для удаления!');
            }
        } else {
            showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        showError('Ошибка! Удалять комментарии могут только модераторы!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `comments` WHERE relate_type=? AND `relate_id`=? LIMIT 1;", ['down', $id]);

    if (!empty($query)) {

        $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
        $end = ceil($total_comments / setting('downcomm'));

        redirect("/load/down?act=comments&id=$id&page=$end");
    } else {
        showError('Ошибка! Данного файла не существует!');
    }

break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br>';

view(setting('themes').'/foot');
