<?php

switch ($action):




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
