<?php

switch ($action):


    ############################################################################################
    ##                                 Добавление комментариев                                ##
    ############################################################################################
    case 'comments':

        $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", [$id]);
        if (!empty($queryoff)) {
            //setting('newtitle') = 'Комментарии - '.$queryoff['title'];

            echo '<i class="fa fa-comment"></i> <b><a href="/offers?act=view&amp;id='.$queryoff['id'].'">'.$queryoff['title'].'</a></b><br><br>';

            echo '<a href="/offers?type=0">Предложения</a> / ';
            echo '<a href="/offers?type=1">Проблемы</a> / ';
            echo '<a href="/offers?act=end&amp;id='.$id.'">Обновить</a><hr>';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=? AND `relate_id`=?;", ['offer', $id]);
            $page = paginate(setting('postcommoffers'), $total);

            if ($total > 0) {

                $is_admin = isAdmin();

                if ($is_admin) {
                    echo '<form action="/offers?act=delcomm&amp;id='.$id.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';
                }

                $querycomm = DB::select("SELECT * FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` ASC LIMIT ".$page['offset'].", ".setting('postcommoffers').";", ['offer', $id]);

                while ($data = $querycomm -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.userAvatar($data['user']).'</div>';

                    if ($is_admin) {
                        echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['id'].'"></span>';
                    }

                    echo '<b>'.profile($data['user']).'</b> <small>('.dateFixed($data['time']).')</small><br>';
                    echo userStatus($data['user']).' '.userOnline($data['user']).'</div>';

                    echo '<div>'.bbCode($data['text']).'<br>';

                    if ($is_admin) {
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
                if (empty($queryoff['closed'])) {
                    echo '<div class="form">';
                    echo '<form action="/offers?act=addcomm&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo '<b>Комментарий:</b><br>';
                    echo '<textarea cols="25" rows="5" name="msg"></textarea><br>';
                    echo '<input type="submit" value="Написать"></form></div><br>';

                    echo '<a href="/rules">Правила</a> / ';
                    echo '<a href="/smiles">Смайлы</a> / ';
                    echo '<a href="/tags">Теги</a><br><br>';
                } else {
                    showError('Комментирование данного предложения или проблемы закрыто!');
                }
            } else {
                showError('Для добавления сообщения необходимо авторизоваться');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=view&amp;id='.$queryoff['id'].'">Вернуться</a><br>';
        } else {
            showError('Ошибка! Данного предложения или проблемы не существует!');
        }
    break;

    ############################################################################################
    ##                                   Запись комментариев                                  ##
    ############################################################################################
    case 'addcomm':

        $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
        $msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';

        //setting('newtitle') = 'Добавление комментария';

        if (getUser()) {
            if ($uid == $_SESSION['token']) {
                if (utfStrlen($msg) >= 5 && utfStrlen($msg) <= 1000) {
                    $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", [$id]);
                    if (!empty($queryoff)) {
                        if (empty($queryoff['closed'])) {
                            if (Flood::isFlood()) {

                                $msg = antimat($msg);

                                DB::insert("INSERT INTO `comments` (relate_type, relate_category_id, `relate_id`, `text`, `user`, `time`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", ['offer', 0, $id, $msg, getUser('login'), SITETIME, getIp(), getBrowser()]);

                                DB::update("UPDATE `offers` SET `comments`=`comments`+1 WHERE `id`=?;", [$id]);

                                setFlash('success', 'Комментарий успешно добавлен!');
                                redirect("/offers?act=end&id=$id");
                            } else {
                                showError('Антифлуд! Разрешается отправлять сообщения раз в '.Flood::getPeriod().' секунд!');
                            }
                        } else {
                            showError('Комментирование данного предложения или проблемы закрыто!');
                        }
                    } else {
                        showError('Ошибка! Данного предложения или проблемы не существует!');
                    }
                } else {
                    showError('Ошибка! Слишком длинный или короткий комментарий!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            showError('Для добавления сообщения необходимо авторизоваться');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=comments&amp;id='.$id.'">Вернуться</a><br>';
    break;

    ############################################################################################
    ##                                          Голосование                                   ##
    ############################################################################################
    case 'vote':

        $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';

        if (getUser()) {
            if ($uid == $_SESSION['token']) {
                $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", [$id]);
                if (!empty($queryoff)) {
                    if ($queryoff['status'] <= 1) {
                        if (getUser('login') != $queryoff['user']) {
                            $queryrated = DB::run() -> querySingle("SELECT `id` FROM `pollings` WHERE relate_type=? AND `relate_id`=? AND `user`=? LIMIT 1;", ['offer', $id, getUser('login')]);
                            if (empty($queryrated)) {
                                DB::insert("INSERT INTO `pollings` (relate_type, `relate_id`, `user`, `time`) VALUES (?, ?, ?, ?);", ['offer', $id, getUser('login'), SITETIME]);
                                DB::update("UPDATE `offers` SET `votes`=`votes`+1 WHERE `id`=?;", [$id]);
                            } else {
                                DB::delete("DELETE FROM `pollings` WHERE relate_type=? AND `relate_id`=? AND `user`=? LIMIT 1;", ['offer', $id, getUser('login')]);
                                if ($queryoff['votes'] > 0) {
                                    DB::update("UPDATE `offers` SET `votes`=`votes`-1 WHERE `id`=?;", [$id]);
                                }
                            }

                            setFlash('success', 'Спасибо! Ваш голос учтен!');
                            redirect("/offers?act=view&id=$id");
                        } else {
                            showError('Ошибка! Запрещено голосовать за свое продложение или проблему!');
                        }
                    } else {
                        showError('Ошибка! Данное предложение или проблема уже решена или закрыта!');
                    }
                } else {
                    showError('Ошибка! Данного предложения или проблемы не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            showError('Для голосования необходимо авторизоваться');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=view&amp;id='.$id.'">Вернуться</a><br>';
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

        if (isAdmin()) {
            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    $delcomments = DB::run() -> exec("DELETE FROM `comments` WHERE relate_type='offer' AND `id` IN (".$del.") AND `relate_id`=".$id.";");
                    DB::update("UPDATE `offers` SET `comments`=`comments`-? WHERE `id`=?;", [$delcomments, $id]);

                    setFlash('success', 'Выбранные комментарии успешно удалены!');
                    redirect("/offers?act=comments&id=$id&page=$page");
                } else {
                    showError('Ошибка! Отстутствуют выбранные комментарии для удаления!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            showError('Ошибка! Удалять комментарии могут только модераторы!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers?act=comments&amp;id='.$id.'">Вернуться</a><br>';
    break;

    ############################################################################################
    ##                             Переадресация на последнюю страницу                        ##
    ############################################################################################
    case 'end':

        $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `comments` WHERE relate_type=? AND `relate_id`=? LIMIT 1;", ['offer', $id]);

        if (!empty($query)) {
            $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
            $end = ceil($total_comments / setting('postcommoffers'));

            redirect("/offers?act=comments&id=$id&page=$end");
        } else {
            showError('Ошибка! Данного предложения или проблемы не существует!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/offers">Вернуться</a><br>';
    break;

endswitch;

view(setting('themes').'/foot');
