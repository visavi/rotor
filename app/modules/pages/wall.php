<?php
view(setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$uz = (empty($_GET['uz'])) ? check(getUsername()) : check($_GET['uz']);
$page = abs(intval(Request::input('page', 1)));

//show_title('Стена сообщений');

$queryuser = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
if (!empty($queryuser)) {
    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            //setting('newtitle') = 'Стена пользователя '.$uz;
            echo '<i class="fa fa-sticky-note"></i> <b>Стена  пользователя '.$uz.'</b><br><br>';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `wall` WHERE `user`=?;", [$uz]);
            $page = paginate(setting('wallpost'), $total);

            if ($uz == getUsername() && user('newwall') > 0) {
                echo '<div style="text-align:center"><b><span style="color:#ff0000">Новых записей: '.user('newwall').'</span></b></div>';
                DB::run() -> query("UPDATE `users` SET `newwall`=? WHERE `login`=?;", [0, getUsername()]);
            }

            if ($total > 0) {

                $is_admin = isAdmin();

                if ($is_admin) {
                    echo '<form action="/wall?act=del&amp;uz='.$uz.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';
                } elseif ($uz == getUsername()) {
                    echo '<form action="/wall?act=delete&amp;uz='.$uz.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';
                }

                $querywall = DB::run() -> query("SELECT * FROM `wall` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".setting('wallpost').";", [$uz]);

                while ($data = $querywall -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.userAvatar($data['login']).'</div>';

                    if ($is_admin || $uz == getUsername()) {
                        echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['id'].'"></span>';
                    }

                    echo '<b>'.profile($data['login']).'</b> <small>('.dateFixed($data['time']).')</small><br>';
                    echo userStatus($data['login']).' '.userOnline($data['login']).'</div>';

                    if ($uz == getUsername() && getUsername() != $data['login']) {
                        echo '<div class="right">';
                        echo '<a href="/private?act=submit&amp;uz='.$data['login'].'">Приват</a> / ';
                        echo '<a href="/wall?uz='.$data['login'].'">Стена</a> / ';
                        echo '<a href="/wall?act=spam&amp;id='.$data['id'].'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете факт спама?\')" rel="nofollow">Спам</a></div>';
                    }

                    echo '<div>'.bbCode($data['text']).'</div>';
                }

                if ($is_admin || $uz == getUsername()) {
                    echo '<span class="imgright"><input type="submit" value="Удалить выбранное"></span></form>';
                }

                pagination($page);
            } else {
                showError('Записок еще нет!');
            }

            if (isUser()) {

                echo '<div class="form">';
                echo '<form action="/wall?act=add&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Сообщение:<br>';
                echo '<textarea cols="25" rows="5" name="msg"></textarea><br>';
                echo '<input type="submit" value="Написать"></form></div><br>';

            } else {
                showError('Для добавления сообщения необходимо авторизоваться');
            }

            echo 'Всего записей: <b>'.$total.'</b><br><br>';
        break;

        ############################################################################################
        ##                                    Добавление сообщения                                ##
        ############################################################################################
        case 'add':

            $uid = check($_GET['uid']);
            $msg = check($_POST['msg']);

            if (isUser()) {
                if ($uz == getUsername() || isAdmin() || is_contact($uz, getUsername())){
                    if ($uid == $_SESSION['token']) {
                        if (utfStrlen($msg) >= 5 && utfStrlen($msg) < 1000) {
                            $ignorstr = DB::run() -> querySingle("SELECT `id` FROM ignoring WHERE `user`=? AND `name`=? LIMIT 1;", [$uz, getUsername()]);
                            if (empty($ignorstr)) {
                                if (Flood::isFlood()) {

                                    $msg = antimat($msg);

                                    if ($uz != getUsername()) {
                                        DB::run() -> query("UPDATE `users` SET `newwall`=`newwall`+1 WHERE `login`=?", [$uz]);
                                    }

                                    DB::run() -> query("INSERT INTO `wall` (`user`, `login`, `text`, `time`) VALUES (?, ?, ?, ?);", [$uz, getUsername(), $msg, SITETIME]);

                                    DB::run() -> query("DELETE FROM `wall` WHERE `user`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `wall` WHERE `user`=? ORDER BY `time` DESC LIMIT ".setting('wallmaxpost').") AS del);", [$uz, $uz]);

                                    setFlash('success', 'Запись успешно добавлена!');
                                    redirect("/wall?uz=$uz");
                                } else {
                                    showError('Антифлуд! Разрешается отправлять сообщения раз в '.Flood::getPeriod().' секунд!');
                                }
                            } else {
                                showError('Ошибка! Вы внесены в игнор-лист пользователя!');
                            }
                        } else {
                            showError('Ошибка! Слишком длинное или короткое сообщение!');
                        }
                    } else {
                        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                    }
                } else {
                    showError('Стена закрыта, писать могут только пользователи из контактов!');
                }
            } else {
                showError('Для добавления сообщения необходимо авторизоваться');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Жалоба на спам                                      ##
        ############################################################################################
        case 'spam':

            $uid = check($_GET['uid']);
            $id = abs(intval($_GET['id']));

            if (isUser()) {
                if ($uid == $_SESSION['token']) {
                    $data = DB::run() -> queryFetch("SELECT * FROM `wall` WHERE `user`=? AND `id`=? LIMIT 1;", [getUsername(), $id]);

                    if (!empty($data)) {
                        $queryspam = DB::run() -> querySingle("SELECT `id` FROM `spam` WHERE relate=? AND `idnum`=? LIMIT 1;", [4, $id]);

                        if (empty($queryspam)) {
                            if (Flood::isFlood()) {
                                DB::run() -> query("INSERT INTO `spam` (relate, `idnum`, `user`, `login`, `text`, `time`, `addtime`, `link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", [4, $data['id'], getUsername(), $data['login'], $data['text'], $data['time'], SITETIME, setting('home').'/wall?uz='.$uz.'&amp;page='.$page]);

                                setFlash('success', 'Жалоба успешно отправлена!');
                                redirect("/wall?uz=$uz&page=$page");
                            } else {
                                showError('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.Flood::getPeriod().' секунд!');
                            }
                        } else {
                            showError('Ошибка! Вы уже отправили жалобу на данное сообщение!');
                        }
                    } else {
                        showError('Ошибка! Данное сообщение написано не на вашей стене!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Для отправки жалобы необходимо авторизоваться');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Пользовательское удаление                              ##
        ############################################################################################
        case 'delete':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uz == getUsername()) {
                if ($uid == $_SESSION['token']) {
                    if (!empty($del)) {
                        $del = implode(',', $del);

                        $delcomments = DB::run() -> query("DELETE FROM `wall` WHERE `id` IN (".$del.") AND `user`=?;", [getUsername()]);

                        setFlash('success', 'Выбранные записи успешно удалены!');
                        redirect("/wall?uz=$uz&page=$page");
                    } else {
                        showError('Ошибка! Отстутствуют выбранные сообщения для удаления!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Нельзя удалять записи на чужой стене!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br>';
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

                        $delcomments = DB::run() -> query("DELETE FROM `wall` WHERE `id` IN (".$del.");");

                        setFlash('success', 'Выбранные записи успешно удалены!');
                        redirect("/wall?uz=$uz&page=$page");
                    } else {
                        showError('Ошибка! Отстутствуют выбранные сообщения для удаления!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Удалять записи могут только модераторы!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

    endswitch;

} else {
    showError('Ошибка! Пользователь с данным логином  не зарегистрирован!');
}

view(setting('themes').'/foot');
