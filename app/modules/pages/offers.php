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
if (isset($_GET['sort'])) {
    $sort = check($_GET['sort']);
} else {
    $sort = 'vote';
}
if (isset($_GET['id'])) {
    $id = abs(intval($_GET['id']));
} else {
    $id = 0;
}
if (isset($_GET['type'])) {
    $type = abs(intval($_GET['type']));
} else {
    $type = 0;
}

show_title('Предложения и проблемы');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
    case 'index':
        $type2 = (empty($type))? 1 : 0;

        $total = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `type`=?;", array($type));
        $total2 = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `type`=?;", array($type2));

        echo '<i class="fa fa-book"></i> ';

        if (empty($type)) {
            echo '<b>Предложения</b> ('.$total.') / <a href="/offers?type=1">Проблемы</a> ('.$total2.')';
        } else {
            echo '<a href="/offers?type=0">Предложения</a> ('.$total2.') / <b>Проблемы</b> ('.$total.')';
        }

        if (is_admin(array(101, 102))) {
            echo ' / <a href="/admin/offers?type='.$type.'&amp;start='.$start.'">Управление</a>';
        }

        switch ($sort) {
            case 'time': $order = 'time';
                break;
            case 'stat': $order = 'status';
                break;
            case 'comm': $order = 'comments';
                break;
            default: $order = 'votes';
        }

        echo '<br />Сортировать: ';

        if ($order == 'votes') {
            echo '<b>Голоса</b> / ';
        } else {
            echo '<a href="/offers?type='.$type.'&amp;sort=vote">Голоса</a> / ';
        }

        if ($order == 'time') {
            echo '<b>Дата</b> / ';
        } else {
            echo '<a href="/offers?type='.$type.'&amp;sort=time">Дата</a> / ';
        }

        if ($order == 'status') {
            echo '<b>Статус</b> / ';
        } else {
            echo '<a href="/offers?type='.$type.'&amp;sort=stat">Статус</a> / ';
        }

        if ($order == 'comments') {
            echo '<b>Комментарии</b>';
        } else {
            echo '<a href="/offers?type='.$type.'&amp;sort=comm">Комментарии</a>';
        }

        echo '<hr />';

        if ($total > 0) {
            if ($start >= $total) {
                $start = 0;
            }

            $queryoffers = DB::run() -> query("SELECT * FROM `offers` WHERE `type`=? ORDER BY ".$order." DESC LIMIT ".$start.", ".$config['postoffers'].";", array($type));

            while ($data = $queryoffers -> fetch()) {
                echo '<div class="b">';
                echo '<i class="fa fa-file-o"></i> ';
                echo '<b><a href="/offers?act=view&amp;type='.$type.'&amp;id='.$data['id'].'">'.$data['title'].'</a></b> (Голосов: '.$data['votes'].')<br />';

                switch ($data['status']) {
                    case '1': echo '<i class="fa fa-spinner"></i> <b><span style="color:#0000ff">В процессе</span></b>';
                        break;
                    case '2': echo '<i class="fa fa-check-circle"></i> <b><span style="color:#00cc00">Выполнено</span></b>';
                        break;
                    case '3': echo '<i class="fa fa-times-circle"></i> <b><span style="color:#ff0000">Закрыто</span></b>';
                        break;
                    default: echo '<i class="fa fa-question-circle"></i> <b><span style="color:#ffa500">Под вопросом</span></b>';
                }

                echo '</div>';
                echo '<div>'.bb_code($data['text']).'<br />';
                echo 'Добавлено: '.profile($data['user']).' ('.date_fixed($data['time']).')<br />';
                echo '<a href="/offers?act=comments&amp;id='.$data['id'].'">Комментарии</a> ('.$data['comments'].') ';
                echo '<a href="/offers?act=end&amp;id='.$data['id'].'">&raquo;</a></div>';
            }

            page_strnavigation('/offers?type='.$type.'&amp;sort='.$sort.'&amp;', $config['postoffers'], $start, $total);

            echo 'Всего записей: <b>'.$total.'</b><br /><br />';
        } else {
            show_error('Записей еще нет!');
        }

        echo '<i class="fa fa-check"></i> <a href="/offers?act=new">Добавить</a><br />';
    break;

    ############################################################################################
    ##                                     Просмотр записи                                    ##
    ############################################################################################
    case 'view':

        $total = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `type`=?;", array(0));
        $total2 = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `type`=?;", array(1));

        echo '<i class="fa fa-book"></i> <a href="/offers?type=0">Предложения</a>  ('.$total.') / ';
        echo '<a href="/offers?type=1">Проблемы</a> ('.$total2.')';

        if (is_admin(array(101, 102))) {
            echo ' / <a href="/admin//offers?act=view&amp;id='.$id.'">Управление</a>';
        }
        echo '<hr />';

        $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", array($id));
        if (!empty($queryoff)) {
            $config['newtitle'] = $queryoff['title'];

            echo '<div class="b">';
            echo '<i class="fa fa-file-o"></i> ';
            echo '<b>'.$queryoff['title'].'</b> (Голосов: '.$queryoff['votes'].')<br />';

            switch ($queryoff['status']) {
                case '1': echo '<i class="fa fa-spinner"></i> <b><span style="color:#0000ff">В процессе</span></b>';
                    break;
                case '2': echo '<i class="fa fa-check-circle"></i> <b><span style="color:#00cc00">Выполнено</span></b>';
                    break;
                case '3': echo '<i class="fa fa-times-circle"></i> <b><span style="color:#ff0000">Закрыто</span></b>';
                    break;
                default: echo '<i class="fa fa-question-circle"></i> <b><span style="color:#ffa500">Под вопросом</span></b>';
            }

            echo '</div>';

            if ($queryoff['status'] <= 1 && $log == $queryoff['user']) {
                echo '<div class="right"><a href="/offers?act=edit&amp;id='.$id.'">Редактировать</a></div>';
            }

            echo '<div>'.bb_code($queryoff['text']).'<br />';
            echo 'Добавлено: '.profile($queryoff['user']).' ('.date_fixed($queryoff['time']).')<br />';

            if ($queryoff['status'] <= 1 && $log != $queryoff['user']) {
                $queryrated = DB::run() -> querySingle("SELECT `id` FROM `ratedoffers` WHERE `offers`=? AND `user`=? LIMIT 1;", array($id, $log));

                if (empty($queryrated)) {
                    echo '<b><a href="/offers?act=vote&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'"><i class="fa fa-thumbs-up"></i> Согласен</a></b><br />';
                } else {
                    echo '<b><a href="/offers?act=vote&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'"><i class="fa fa-thumbs-down"></i> Передумал</a></b><br />';
                }
            }

            echo '<a href="/offers?act=comments&amp;id='.$id.'">Комментарии</a> ('.$queryoff['comments'].') ';
            echo '<a href="/offers?act=end&amp;id='.$id.'">&raquo;</a></div><br />';

            if (!empty($queryoff['text_reply'])) {
                echo '<div class="b"><b>Официальный ответ</b></div>';
                echo '<div class="q">'.bb_code($queryoff['text_reply']).'<br />';
                echo profile($queryoff['user_reply']).' ('.date_fixed($queryoff['time_reply']).')</div><br />';
            }
            // ------------------------------------------------//
            echo '<div class="b"><i class="fa fa-comment"></i> <b>Последние комментарии</b></div><br />';

            if ($queryoff['comments'] > 0) {
                $querycomm = DB::run() -> query("SELECT * FROM `commoffers` WHERE `comm_offers`=? ORDER BY `comm_time` DESC LIMIT 5;", array($id));

                while ($comm = $querycomm -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($comm['comm_user']).'</div>';

                    echo '<b>'.profile($comm['comm_user']).'</b>';
                    echo '<small> ('.date_fixed($comm['comm_time']).')</small><br />';
                    echo user_title($comm['comm_user']).' '.user_online($comm['comm_user']).'</div>';

                    echo '<div>'.bb_code($comm['comm_text']).'<br />';

                    if (is_admin() || empty($config['anonymity'])) {
                        echo '<span class="data">('.$comm['comm_brow'].', '.$comm['comm_ip'].')</span>';
                    }

                    echo '</div>';
                }
                echo '<br />';
            } else {
                show_error('Комментариев еще нет!');
            }

            if (is_user()) {
                if (empty($queryoff['closed'])) {
                    echo '<div class="form"><form action="/offers?act=addcomm&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo '<b>Комментарий:</b><br />';
                    echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';
                    echo '<input type="submit" value="Написать" /></form></div>';

                    echo '<br />';
                    echo '<a href="/rules">Правила</a> / ';
                    echo '<a href="/smiles">Смайлы</a> / ';
                    echo '<a href="/tags">Теги</a><br /><br />';
                } else {
                    show_error('Комментирование данного предложения или проблемы закрыто!');
                }
            } else {
                show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо');
            }
        } else {
            show_error('Ошибка! Данного предложения или проблемы не существует!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?type='.$type.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                 Редактирование предложения                             ##
    ############################################################################################
    case 'edit':

        if (is_user()) {
            $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? AND `user`=? LIMIT 1;", array($id, $log));
            if (!empty($queryoff)) {
                if ($queryoff['status'] <= 1) {

                    echo '<div class="form">';
                    echo '<form action="/offers?act=change&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                    echo 'Тип:<br />';
                    echo '<select name="types">';
                    $selected = ($queryoff['type'] == 0) ? ' selected="selected"' : '';
                    echo '<option value="0"'.$selected.'>Предложение</option>';
                    $selected = ($queryoff['type'] == 1) ? ' selected="selected"' : '';
                    echo '<option value="1"'.$selected.'>Проблема</option>';
                    echo '</select><br />';

                    echo 'Заголовок: <br /><input type="text" name="title" value="'.$queryoff['title'].'" /><br />';
                    echo 'Описание: <br /><textarea cols="25" rows="5" name="text">'.$queryoff['text'].'</textarea><br />';

                    echo '<input type="submit" value="Изменить" /></form></div><br />';
                } else {
                    show_error('Ошибка! Данное предложение или проблема уже решена или закрыта!');
                }
            } else {
                show_error('Ошибка! Данного предложения или проблемы не существует!');
            }
        } else {
            show_login('Вы не авторизованы, чтобы редактировать предложение, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=view&amp;id='.$id.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                 Изменение описания                                     ##
    ############################################################################################
    case 'change':

        $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
        $title = (isset($_POST['title'])) ? check($_POST['title']) : '';
        $text = (isset($_POST['text'])) ? check($_POST['text']) : '';
        $types = (empty($_POST['types'])) ? 0 : 1;

        if ($uid == $_SESSION['token']) {
            if (is_user()) {
                $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? AND `user`=? LIMIT 1;", array($id, $log));
                if (!empty($queryoff)) {
                    if ($queryoff['status'] <= 1) {
                        if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                            if (utf_strlen($text) >= 5 && utf_strlen($text) <= 1000) {

                                $title = antimat($title);
                                $text = antimat($text);

                                DB::run() -> query("UPDATE `offers` SET `type`=?, `title`=?, `text`=? WHERE `id`=?;", array($types, $title, $text, $id));

                                notice('Данные успешно отредактированы!');
                                redirect("/offers?act=view&type=$types&id=$id");
                            } else {
                                show_error('Ошибка! Слишком длинное или короткое описание (От 5 до 1000 символов)!');
                            }
                        } else {
                            show_error('Ошибка! Слишком длинный или короткий заголовок (От 5 до 50 символов)!');
                        }
                    } else {
                        show_error('Ошибка! Данное предложение или проблема уже решена или закрыта!');
                    }
                } else {
                    show_error('Ошибка! Данного предложения или проблемы не существует!');
                }
            } else {
                show_login('Вы не авторизованы, чтобы редактировать предложение, необходимо');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=edit&amp;id='.$id.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                 Добавление комментариев                                ##
    ############################################################################################
    case 'comments':

        $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", array($id));
        if (!empty($queryoff)) {
            $config['newtitle'] = 'Комментарии - '.$queryoff['title'];

            echo '<i class="fa fa-comment"></i> <b><a href="/offers?act=view&amp;id='.$queryoff['id'].'">'.$queryoff['title'].'</a></b><br /><br />';

            echo '<a href="/offers?type=0">Предложения</a> / ';
            echo '<a href="/offers?type=1">Проблемы</a> / ';
            echo '<a href="/offers?act=end&amp;id='.$id.'">Обновить</a><hr />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `commoffers` WHERE `comm_offers`=?;", array($id));

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $is_admin = is_admin();

                if ($is_admin) {
                    echo '<form action="/offers?act=delcomm&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                }

                $querycomm = DB::run() -> query("SELECT * FROM `commoffers` WHERE `comm_offers`=? ORDER BY `comm_time` ASC LIMIT ".$start.", ".$config['postcommoffers'].";", array($id));

                while ($data = $querycomm -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['comm_user']).'</div>';

                    if ($is_admin) {
                        echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['comm_id'].'" /></span>';
                    }

                    echo '<b>'.profile($data['comm_user']).'</b> <small>('.date_fixed($data['comm_time']).')</small><br />';
                    echo user_title($data['comm_user']).' '.user_online($data['comm_user']).'</div>';

                    echo '<div>'.bb_code($data['comm_text']).'<br />';

                    if ($is_admin || empty($config['anonymity'])) {
                        echo '<span class="data">('.$data['comm_brow'].', '.$data['comm_ip'].')</span>';
                    }

                    echo '</div>';
                }

                if ($is_admin) {
                    echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
                }

                page_strnavigation('/offers?act=comments&amp;id='.$id.'&amp;', $config['postcommoffers'], $start, $total);
            } else {
                show_error('Комментариев еще нет!');
            }

            if (is_user()) {
                if (empty($queryoff['closed'])) {
                    echo '<div class="form">';
                    echo '<form action="/offers?act=addcomm&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo '<b>Комментарий:</b><br />';
                    echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';
                    echo '<input type="submit" value="Написать" /></form></div><br />';

                    echo '<a href="/rules">Правила</a> / ';
                    echo '<a href="/smiles">Смайлы</a> / ';
                    echo '<a href="/tags">Теги</a><br /><br />';
                } else {
                    show_error('Комментирование данного предложения или проблемы закрыто!');
                }
            } else {
                show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=view&amp;id='.$queryoff['id'].'">Вернуться</a><br />';
        } else {
            show_error('Ошибка! Данного предложения или проблемы не существует!');
        }
    break;

    ############################################################################################
    ##                                   Запись комментариев                                  ##
    ############################################################################################
    case 'addcomm':

        $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
        $msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';

        $config['newtitle'] = 'Добавление комментария';

        if (is_user()) {
            if ($uid == $_SESSION['token']) {
                if (utf_strlen($msg) >= 5 && utf_strlen($msg) <= 1000) {
                    $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", array($id));
                    if (!empty($queryoff)) {
                        if (empty($queryoff['closed'])) {
                            if (is_flood($log)) {

                                $msg = antimat($msg);

                                DB::run() -> query("INSERT INTO `commoffers` (`comm_offers`, `comm_text`, `comm_user`, `comm_time`, `comm_ip`, `comm_brow`) VALUES (?, ?, ?, ?, ?, ?);", array($id, $msg, $log, SITETIME, App::getClientIp(), App::getUserAgent()));

                                DB::run() -> query("DELETE FROM `commoffers` WHERE `comm_offers`=? AND `comm_time` < (SELECT MIN(`comm_time`) FROM (SELECT `comm_time` FROM `commoffers` WHERE `comm_id`=? ORDER BY `comm_time` DESC LIMIT ".$config['maxpostoffers'].") AS del);", array($id, $id));

                                DB::run() -> query("UPDATE `offers` SET `comments`=`comments`+1 WHERE `id`=?;", array($id));

                                notice('Комментарий успешно добавлен!');
                                redirect("/offers?act=end&id=$id");
                            } else {
                                show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
                            }
                        } else {
                            show_error('Комментирование данного предложения или проблемы закрыто!');
                        }
                    } else {
                        show_error('Ошибка! Данного предложения или проблемы не существует!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий комментарий!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=comments&amp;id='.$id.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                          Голосование                                   ##
    ############################################################################################
    case 'vote':

        $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';

        if (is_user()) {
            if ($uid == $_SESSION['token']) {
                $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", array($id));
                if (!empty($queryoff)) {
                    if ($queryoff['status'] <= 1) {
                        if ($log != $queryoff['user']) {
                            $queryrated = DB::run() -> querySingle("SELECT `id` FROM `ratedoffers` WHERE `offers`=? AND `user`=? LIMIT 1;", array($id, $log));
                            if (empty($queryrated)) {
                                DB::run() -> query("INSERT INTO `ratedoffers` (`offers`, `user`, `time`) VALUES (?, ?, ?);", array($id, $log, SITETIME));
                                DB::run() -> query("UPDATE `offers` SET `votes`=`votes`+1 WHERE `id`=?;", array($id));
                            } else {
                                DB::run() -> query("DELETE FROM `ratedoffers` WHERE `offers`=? AND `user`=? LIMIT 1;", array($id, $log));
                                if ($queryoff['votes'] > 0) {
                                    DB::run() -> query("UPDATE `offers` SET `votes`=`votes`-1 WHERE `id`=?;", array($id));
                                }
                            }

                            notice('Спасибо! Ваш голос учтен!');
                            redirect("/offers?act=view&id=$id");
                        } else {
                            show_error('Ошибка! Запрещено голосовать за свое продложение или проблему!');
                        }
                    } else {
                        show_error('Ошибка! Данное предложение или проблема уже решена или закрыта!');
                    }
                } else {
                    show_error('Ошибка! Данного предложения или проблемы не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_login('Вы не авторизованы, для голосования за предложения и проблемы, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=view&amp;id='.$id.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                 Подготовка к добавлению                                ##
    ############################################################################################
    case 'new':
        echo '<b><big>Добавление</big></b><br /><br />';

        if ($udata['point'] >= $config['addofferspoint']) {
            echo '<div class="form">';
            echo '<form action="/offers?act=add&amp;uid='.$_SESSION['token'].'" method="post">';

            echo 'Я хотел бы...<br />';
            echo '<select name="types">';
            echo '<option value="0">Предложить идею</option><option value="1">Сообщить о проблеме</option>';
            echo '</select><br />';

            echo 'Заголовок: <br />';
            echo '<input type="text" name="title" maxlength="50" /><br />';
            echo 'Описание:<br />';
            echo '<textarea cols="25" rows="5" name="text"></textarea><br />';
            echo '<input type="submit" value="Добавить" /></form></div><br />';
        } else {
            show_error('Ошибка! Для добавления предложения или проблемы вам необходимо набрать '.points($config['addofferspoint']).'!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                         Добавление                                     ##
    ############################################################################################
    case 'add':

        $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
        $title = (isset($_POST['title'])) ? check($_POST['title']) : '';
        $text = (isset($_POST['text'])) ? check($_POST['text']) : '';
        $types = (empty($_POST['types'])) ? 0 : 1;

        if ($uid == $_SESSION['token']) {
            if ($udata['point'] >= $config['addofferspoint']) {
                if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                    if (utf_strlen($text) >= 5 && utf_strlen($text) <= 1000) {
                        if (is_flood($log)) {

                            $title = antimat($title);
                            $text = antimat($text);

                            DB::run() -> query("INSERT INTO `offers` (`type`, `title`, `text`, `user`, `votes`, `time`) VALUES (?, ?, ?, ?, ?, ?);", array($types, $title, $text, $log, 1, SITETIME));
                            $lastid = DB::run() -> lastInsertId();

                            DB::run() -> query("INSERT INTO `ratedoffers` (`offers`, `user`, `time`) VALUES (?, ?, ?);", array($lastid, $log, SITETIME));

                            notice('Сообщение успешно добавлено!');
                            redirect("/offers?act=view&type=$types&id=$lastid");
                        } else {
                            show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинное или короткое описание (От 5 до 1000 символов)!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий заголовок (От 5 до 50 символов)!');
                }
            } else {
                show_error('Ошибка! Для добавления предложения или проблемы вам необходимо набрать '.points($config['addofferspoint']).'!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=new">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                 Удаление комментариев                                  ##
    ############################################################################################
    case 'delcomm':

        $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
        if (isset($_POST['del'])) {
            $del = intar($_POST['del']);
        } else {
            $del = 0;
        }

        if (is_admin()) {
            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    $delcomments = DB::run() -> exec("DELETE FROM `commoffers` WHERE `comm_id` IN (".$del.") AND `comm_offers`=".$id.";");
                    DB::run() -> query("UPDATE `offers` SET `comments`=`comments`-? WHERE `id`=?;", array($delcomments, $id));

                    notice('Выбранные комментарии успешно удалены!');
                    redirect("/offers?act=comments&id=$id&start=$start");
                } else {
                    show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_error('Ошибка! Удалять комментарии могут только модераторы!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=comments&amp;id='.$id.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                             Переадресация на последнюю страницу                        ##
    ############################################################################################
    case 'end':

        $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `commoffers` WHERE `comm_offers`=? LIMIT 1;", array($id));

        if (!empty($query)) {
            $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
            $end = last_page($total_comments, $config['postcommoffers']);

            redirect("/offers?act=comments&id=$id&start=$end");
        } else {
            show_error('Ошибка! Данного предложения или проблемы не существует!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers">Вернуться</a><br />';
    break;

endswitch;

App::view($config['themes'].'/foot');
