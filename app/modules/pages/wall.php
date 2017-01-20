<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$uz = (empty($_GET['uz'])) ? check($log) : check($_GET['uz']);
$page = abs(intval(Request::input('page', 1)));

show_title('Стена сообщений');

$queryuser = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
if (!empty($queryuser)) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $config['newtitle'] = 'Стена пользователя '.nickname($uz);
            echo '<i class="fa fa-sticky-note"></i> <b>Стена  пользователя '.nickname($uz).'</b><br /><br />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `wall` WHERE `user`=?;", [$uz]);
            $page = App::paginate(App::setting('wallpost'), $total);

            if ($uz == $log && $udata['newwall'] > 0) {
                echo '<div style="text-align:center"><b><span style="color:#ff0000">Новых записей: '.$udata['newwall'].'</span></b></div>';
                DB::run() -> query("UPDATE `users` SET `newwall`=? WHERE `login`=?;", [0, $log]);
            }

            if ($total > 0) {

                $is_admin = is_admin();

                if ($is_admin) {
                    echo '<form action="/wall?act=del&amp;uz='.$uz.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';
                } elseif ($uz == $log) {
                    echo '<form action="/wall?act=delete&amp;uz='.$uz.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';
                }

                $querywall = DB::run() -> query("SELECT * FROM `wall` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['wallpost'].";", [$uz]);

                while ($data = $querywall -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['login']).'</div>';

                    if ($is_admin || $uz == $log) {
                        echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['id'].'" /></span>';
                    }

                    echo '<b>'.profile($data['login']).'</b> <small>('.date_fixed($data['time']).')</small><br />';
                    echo user_title($data['login']).' '.user_online($data['login']).'</div>';

                    if ($uz == $log && $log != $data['login']) {
                        echo '<div class="right">';
                        echo '<a href="/private?act=submit&amp;uz='.$data['login'].'">Приват</a> / ';
                        echo '<a href="/wall?uz='.$data['login'].'">Стена</a> / ';
                        echo '<noindex><a href="/wall?act=spam&amp;id='.$data['id'].'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете факт спама?\')" rel="nofollow">Спам</a></noindex></div>';
                    }

                    echo '<div>'.App::bbCode($data['text']).'</div>';
                }

                if ($is_admin || $uz == $log) {
                    echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
                }

                App::pagination($page);
            } else {
                show_error('Записок еще нет!');
            }

            if (is_user()) {
                if (!user_privacy($uz) || $uz == $log || is_admin() || is_contact($uz, $log)){
                    echo '<div class="form">';
                    echo '<form action="/wall?act=add&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo 'Сообщение:<br />';
                    echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';
                    echo '<input type="submit" value="Написать" /></form></div><br />';
                } else {
                    show_error('Включен режим приватности, писать могут только пользователи из контактов!');
                }
            } else {
                show_login('Вы не авторизованы, чтобы написать на стене, необходимо');
            }

            echo 'Всего записей: <b>'.$total.'</b><br /><br />';
        break;

        ############################################################################################
        ##                                    Добавление сообщения                                ##
        ############################################################################################
        case 'add':

            $uid = check($_GET['uid']);
            $msg = check($_POST['msg']);

            if (is_user()) {
                if ($uz == $log || is_admin() || is_contact($uz, $log)){
                    if ($uid == $_SESSION['token']) {
                        if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
                            $ignorstr = DB::run() -> querySingle("SELECT `id` FROM ignoring WHERE `user`=? AND `name`=? LIMIT 1;", [$uz, $log]);
                            if (empty($ignorstr)) {
                                if (is_flood($log)) {

                                    $msg = antimat($msg);

                                    if ($uz != $log) {
                                        DB::run() -> query("UPDATE `users` SET `newwall`=`newwall`+1 WHERE `login`=?", [$uz]);
                                    }

                                    DB::run() -> query("INSERT INTO `wall` (`user`, `login`, `text`, `time`) VALUES (?, ?, ?, ?);", [$uz, $log, $msg, SITETIME]);

                                    DB::run() -> query("DELETE FROM `wall` WHERE `user`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `wall` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$config['wallmaxpost'].") AS del);", [$uz, $uz]);

                                    notice('Запись успешно добавлена!');
                                    redirect("/wall?uz=$uz");
                                } else {
                                    show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
                                }
                            } else {
                                show_error('Ошибка! Вы внесены в игнор-лист пользователя!');
                            }
                        } else {
                            show_error('Ошибка! Слишком длинное или короткое сообщение!');
                        }
                    } else {
                        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                    }
                } else {
                    show_error('Стена закрыта, писать могут только пользователи из контактов!');
                }
            } else {
                show_login('Вы не авторизованы, чтобы написать на стене, необходимо');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Жалоба на спам                                      ##
        ############################################################################################
        case 'spam':

            $uid = check($_GET['uid']);
            $id = abs(intval($_GET['id']));

            if (is_user()) {
                if ($uid == $_SESSION['token']) {
                    $data = DB::run() -> queryFetch("SELECT * FROM `wall` WHERE `user`=? AND `id`=? LIMIT 1;", [$log, $id]);

                    if (!empty($data)) {
                        $queryspam = DB::run() -> querySingle("SELECT `id` FROM `spam` WHERE relate=? AND `idnum`=? LIMIT 1;", [4, $id]);

                        if (empty($queryspam)) {
                            if (is_flood($log)) {
                                DB::run() -> query("INSERT INTO `spam` (relate, `idnum`, `user`, `login`, `text`, `time`, `addtime`, `link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", [4, $data['id'], $log, $data['login'], $data['text'], $data['time'], SITETIME, $config['home'].'/wall?uz='.$uz.'&amp;page='.$page]);

                                notice('Жалоба успешно отправлена!');
                                redirect("/wall?uz=$uz&page=$page");
                            } else {
                                show_error('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.flood_period().' секунд!');
                            }
                        } else {
                            show_error('Ошибка! Вы уже отправили жалобу на данное сообщение!');
                        }
                    } else {
                        show_error('Ошибка! Данное сообщение написано не на вашей стене!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_login('Вы не авторизованы, чтобы подать жалобу, необходимо');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br />';
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

            if ($uz == $log) {
                if ($uid == $_SESSION['token']) {
                    if (!empty($del)) {
                        $del = implode(',', $del);

                        $delcomments = DB::run() -> query("DELETE FROM `wall` WHERE `id` IN (".$del.") AND `user`=?;", [$log]);

                        notice('Выбранные записи успешно удалены!');
                        redirect("/wall?uz=$uz&page=$page");
                    } else {
                        show_error('Ошибка! Отстутствуют выбранные сообщения для удаления!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Нельзя удалять записи на чужой стене!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br />';
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

            if (is_admin()) {
                if ($uid == $_SESSION['token']) {
                    if (!empty($del)) {
                        $del = implode(',', $del);

                        $delcomments = DB::run() -> query("DELETE FROM `wall` WHERE `id` IN (".$del.");");

                        notice('Выбранные записи успешно удалены!');
                        redirect("/wall?uz=$uz&page=$page");
                    } else {
                        show_error('Ошибка! Отстутствуют выбранные сообщения для удаления!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Удалять записи могут только модераторы!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br />';
        break;

    endswitch;

} else {
    show_error('Ошибка! Пользователь с данным логином  не зарегистрирован!');
}

App::view($config['themes'].'/foot');
