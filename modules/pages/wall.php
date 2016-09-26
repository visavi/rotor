<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$uz = (empty($_GET['uz'])) ? check($log) : check($_GET['uz']);

show_title('Стена сообщений');

$queryuser = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
if (!empty($queryuser)) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $config['newtitle'] = 'Стена пользователя '.nickname($uz);
            echo '<img src="/images/img/wall.gif" alt="image" /> <b>Стена  пользователя '.nickname($uz).'</b><br /><br />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `wall` WHERE `wall_user`=?;", array($uz));

            if ($uz == $log && $udata['users_newwall'] > 0) {
                echo '<div style="text-align:center"><b><span style="color:#ff0000">Новых записей: '.$udata['users_newwall'].'</span></b></div>';
                DB::run() -> query("UPDATE `users` SET `users_newwall`=? WHERE `users_login`=?;", array(0, $log));
            }

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $is_admin = is_admin();

                if ($is_admin) {
                    echo '<form action="/wall?act=del&amp;uz='.$uz.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                } elseif ($uz == $log) {
                    echo '<form action="/wall?act=delete&amp;uz='.$uz.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                }

                $querywall = DB::run() -> query("SELECT * FROM `wall` WHERE `wall_user`=? ORDER BY `wall_time` DESC LIMIT ".$start.", ".$config['wallpost'].";", array($uz));

                while ($data = $querywall -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['wall_login']).'</div>';

                    if ($is_admin || $uz == $log) {
                        echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['wall_id'].'" /></span>';
                    }

                    echo '<b>'.profile($data['wall_login']).'</b> <small>('.date_fixed($data['wall_time']).')</small><br />';
                    echo user_title($data['wall_login']).' '.user_online($data['wall_login']).'</div>';

                    if ($uz == $log && $log != $data['wall_login']) {
                        echo '<div class="right">';
                        echo '<a href="/private?act=submit&amp;uz='.$data['wall_login'].'">Приват</a> / ';
                        echo '<a href="/wall?uz='.$data['wall_login'].'">Стена</a> / ';
                        echo '<noindex><a href="/wall?act=spam&amp;id='.$data['wall_id'].'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете факт спама?\')" rel="nofollow">Спам</a></noindex></div>';
                    }

                    echo '<div>'.bb_code($data['wall_text']).'</div>';
                }

                if ($is_admin || $uz == $log) {
                    echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
                }

                page_strnavigation('/wall?uz='.$uz.'&amp;', $config['wallpost'], $start, $total);
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
                            $ignorstr = DB::run() -> querySingle("SELECT `ignore_id` FROM `ignore` WHERE `ignore_user`=? AND `ignore_name`=? LIMIT 1;", array($uz, $log));
                            if (empty($ignorstr)) {
                                if (is_quarantine($log)) {
                                    if (is_flood($log)) {

                                        $msg = antimat($msg);

                                        if ($uz != $log) {
                                            DB::run() -> query("UPDATE `users` SET `users_newwall`=`users_newwall`+1 WHERE `users_login`=?", array($uz));
                                        }

                                        DB::run() -> query("INSERT INTO `wall` (`wall_user`, `wall_login`, `wall_text`, `wall_time`) VALUES (?, ?, ?, ?);", array($uz, $log, $msg, SITETIME));

                                        DB::run() -> query("DELETE FROM `wall` WHERE `wall_user`=? AND `wall_time` < (SELECT MIN(`wall_time`) FROM (SELECT `wall_time` FROM `wall` WHERE `wall_user`=? ORDER BY `wall_time` DESC LIMIT ".$config['wallmaxpost'].") AS del);", array($uz, $uz));

                                        $_SESSION['note'] = 'Запись успешно добавлена!';
                                        redirect("/wall?uz=$uz");
                                    } else {
                                        show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
                                    }
                                } else {
                                    show_error('Карантин! Вы не можете писать в течении '.round($config['karantin'] / 3600).' часов!');
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

            echo '<img src="/images/img/back.gif" alt="image" /> <a href="/wall?uz='.$uz.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Жалоба на спам                                      ##
        ############################################################################################
        case 'spam':

            $uid = check($_GET['uid']);
            $id = abs(intval($_GET['id']));

            if (is_user()) {
                if ($uid == $_SESSION['token']) {
                    $data = DB::run() -> queryFetch("SELECT * FROM `wall` WHERE `wall_user`=? AND `wall_id`=? LIMIT 1;", array($log, $id));

                    if (!empty($data)) {
                        $queryspam = DB::run() -> querySingle("SELECT `spam_id` FROM `spam` WHERE `spam_key`=? AND `spam_idnum`=? LIMIT 1;", array(4, $id));

                        if (empty($queryspam)) {
                            if (is_flood($log)) {
                                DB::run() -> query("INSERT INTO `spam` (`spam_key`, `spam_idnum`, `spam_user`, `spam_login`, `spam_text`, `spam_time`, `spam_addtime`, `spam_link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", array(4, $data['wall_id'], $log, $data['wall_login'], $data['wall_text'], $data['wall_time'], SITETIME, $config['home'].'/wall?uz='.$uz.'&amp;start='.$start));

                                $_SESSION['note'] = 'Жалоба успешно отправлена!';
                                redirect("/wall?uz=$uz&start=$start");
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

            echo '<img src="/images/img/back.gif" alt="image" /> <a href="/wall?uz='.$uz.'&amp;start='.$start.'">Вернуться</a><br />';
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

                        $delcomments = DB::run() -> query("DELETE FROM `wall` WHERE `wall_id` IN (".$del.") AND `wall_user`=?;", array($log));

                        $_SESSION['note'] = 'Выбранные записи успешно удалены!';
                        redirect("/wall?uz=$uz&start=$start");
                    } else {
                        show_error('Ошибка! Отстутствуют выбранные сообщения для удаления!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Нельзя удалять записи на чужой стене!');
            }

            echo '<img src="/images/img/back.gif" alt="image" /> <a href="/wall?uz='.$uz.'&amp;start='.$start.'">Вернуться</a><br />';
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

                        $delcomments = DB::run() -> query("DELETE FROM `wall` WHERE `wall_id` IN (".$del.");");

                        $_SESSION['note'] = 'Выбранные записи успешно удалены!';
                        redirect("/wall?uz=$uz&start=$start");
                    } else {
                        show_error('Ошибка! Отстутствуют выбранные сообщения для удаления!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Удалять записи могут только модераторы!');
            }

            echo '<img src="/images/img/back.gif" alt="image" /> <a href="/wall?uz='.$uz.'&amp;start='.$start.'">Вернуться</a><br />';
        break;

    endswitch;

} else {
    show_error('Ошибка! Пользователь с данным логином  не зарегистрирован!');
}

App::view($config['themes'].'/foot');
