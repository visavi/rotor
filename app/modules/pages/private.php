<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$uz = (isset($_REQUEST['uz'])) ? check($_REQUEST['uz']) : '';
$page = abs(intval(Request::input('page', 1)));

show_title('Приватные сообщения');

if (is_user()) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `inbox` WHERE `user`=?;", [$log]);
            $page = App::paginate(App::setting('privatpost'), $total);

            $intotal = DB::run() -> query("SELECT count(*) FROM `outbox` WHERE `author`=? UNION ALL SELECT count(*) FROM `trash` WHERE `user`=?;", [$log, $log]);
            $intotal = $intotal -> fetchAll(PDO::FETCH_COLUMN);

            echo '<i class="fa fa-envelope"></i> <b>Входящие ('.$total.')</b> / ';
            echo '<a href="/private?act=output">Отправленные ('.$intotal[0].')</a> / ';
            echo '<a href="/private?act=trash">Корзина ('.$intotal[1].')</a><hr />';

            if ($udata['newprivat'] > 0) {
                echo '<div style="text-align:center"><b><span style="color:#ff0000">Получено новых писем: '.(int)$udata['newprivat'].'</span></b></div>';
                DB::run() -> query("UPDATE `users` SET `newprivat`=?, `sendprivatmail`=? WHERE `login`=? LIMIT 1;", [0, 0, $log]);
            }

            if ($total >= ($config['limitmail'] - ($config['limitmail'] / 10)) && $total < $config['limitmail']) {
                echo '<div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик почти заполнен, необходимо очистить или удалить старые сообщения!</span></b></div>';
            }

            if ($total >= $config['limitmail']) {
                echo '<div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик переполнен, вы не сможете получать письма пока не очистите его!</span></b></div>';
            }

            if ($total > 0) {

                $querypriv = DB::run() -> query("SELECT * FROM `inbox` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['privatpost'].";", [$log]);

                echo '<form action="/private?act=del&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
                echo '</div>';
                while ($data = $querypriv -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['author']).'</div>';
                    echo '<b>'.profile($data['author']).'</b>  ('.date_fixed($data['time']).')<br />';
                    echo user_title($data['author']).' '.user_online($data['author']).'</div>';

                    echo '<div>'.App::bbCode($data['text']).'<br />';

                    echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';
                    echo '<a href="/private?act=submit&amp;uz='.$data['author'].'">Ответить</a> / ';
                    echo '<a href="/private?act=history&amp;uz='.$data['author'].'">История</a> / ';
                    echo '<a href="/contact?act=add&amp;uz='.$data['author'].'&amp;uid='.$_SESSION['token'].'">В контакт</a> / ';
                    echo '<a href="/ignore?act=add&amp;uz='.$data['author'].'&amp;uid='.$_SESSION['token'].'">Игнор</a> / ';
                    echo '<noindex><a href="/private?act=spam&amp;id='.$data['id'].'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете факт спама?\')" rel="nofollow">Спам</a></noindex></div>';
                }

                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                App::pagination($page);

                echo 'Всего писем: <b>'.(int)$total.'</b><br />';
                echo 'Объем ящика: <b>'.$config['limitmail'].'</b><br /><br />';

                echo '<i class="fa fa-times"></i> <a href="/private?act=alldel&amp;uid='.$_SESSION['token'].'">Очистить ящик</a><br />';
            } else {
                show_error('Входящих писем еще нет!');
            }
        break;

        ############################################################################################
        ##                                 Исходящие сообщения                                    ##
        ############################################################################################
        case 'output':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `outbox` WHERE `author`=?;", [$log]);
            $page = App::paginate(App::setting('privatpost'), $total);

            $intotal = DB::run() -> query("SELECT count(*) FROM `inbox` WHERE `user`=? UNION ALL SELECT count(*) FROM `trash` WHERE `user`=?;", [$log, $log]);
            $intotal = $intotal -> fetchAll(PDO::FETCH_COLUMN);

            echo '<i class="fa fa-envelope"></i> <a href="/private">Входящие ('.$intotal[0].')</a> / ';
            echo '<b>Отправленные ('.$total.')</b> / ';
            echo '<a href="/private?act=trash">Корзина ('.$intotal[1].')</a><hr />';

            if ($total > 0) {

                $querypriv = DB::run() -> query("SELECT * FROM `outbox` WHERE `author`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['privatpost'].";", [$log]);

                echo '<form action="/private?act=outdel&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
                echo '</div>';
                while ($data = $querypriv -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['user']).'</div>';
                    echo '<b>'.profile($data['user']).'</b>  ('.date_fixed($data['time']).')<br />';
                    echo user_title($data['user']).' '.user_online($data['user']).'</div>';

                    echo '<div>'.App::bbCode($data['text']).'<br />';

                    echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';
                    echo '<a href="/private?act=submit&amp;uz='.$data['user'].'">Написать еще</a> / ';
                    echo '<a href="/private?act=history&amp;uz='.$data['user'].'">История</a></div>';
                }

                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                App::pagination($page);

                echo 'Всего писем: <b>'.(int)$total.'</b><br />';
                echo 'Объем ящика: <b>'.$config['limitoutmail'].'</b><br /><br />';

                echo '<i class="fa fa-times"></i> <a href="/private?act=alloutdel&amp;uid='.$_SESSION['token'].'">Очистить ящик</a><br />';
            } else {
                show_error('Отправленных писем еще нет!');
            }
        break;

        ############################################################################################
        ##                                       Корзина                                          ##
        ############################################################################################
        case 'trash':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `trash` WHERE `user`=?;", [$log]);
            $page = App::paginate(App::setting('privatpost'), $total);

            $intotal = DB::run() -> query("SELECT count(*) FROM `inbox` WHERE `user`=? UNION ALL SELECT count(*) FROM `outbox` WHERE `author`=?;", [$log, $log]);
            $intotal = $intotal -> fetchAll(PDO::FETCH_COLUMN);

            echo '<i class="fa fa-envelope"></i> <a href="/private">Входящие ('.$intotal[0].')</a> / ';
            echo '<a href="/private?act=output">Отправленные ('.$intotal[1].')</a> / ';

            echo '<b>Корзина ('.$total.')</b><hr />';
            if ($total > 0) {

                $querypriv = DB::run() -> query("SELECT * FROM `trash` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['privatpost'].";", [$log]);

                while ($data = $querypriv -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['author']).'</div>';
                    echo '<b>'.profile($data['author']).'</b>  ('.date_fixed($data['time']).')<br />';
                    echo user_title($data['author']).' '.user_online($data['author']).'</div>';

                    echo '<div>'.App::bbCode($data['text']).'<br />';

                    echo '<a href="/private?act=submit&amp;uz='.$data['author'].'">Ответить</a> / ';
                    echo '<a href="/contact?act=add&amp;uz='.$data['author'].'&amp;uid='.$_SESSION['token'].'">В контакт</a> / ';
                    echo '<a href="/ignore?act=add&amp;uz='.$data['author'].'&amp;uid='.$_SESSION['token'].'">Игнор</a></div>';
                }

                App::pagination($page);

                echo 'Всего писем: <b>'.(int)$total.'</b><br />';
                echo 'Срок хранения: <b>'.$config['expiresmail'].'</b><br /><br />';

                echo '<i class="fa fa-times"></i> <a href="/private?act=alltrashdel&amp;uid='.$_SESSION['token'].'">Очистить ящик</a><br />';
            } else {
                show_error('Удаленных писем еще нет!');
            }
        break;

        ############################################################################################
        ##                                   Отправка привата                                     ##
        ############################################################################################
        case 'submit':

            if (empty($uz)) {

                echo '<div class="form">';
                echo '<form action="/private?act=send&amp;uid='.$_SESSION['token'].'" method="post">';

                echo 'Введите логин:<br />';
                echo '<input type="text" name="uz" maxlength="20" /><br />';

                $querycontact = DB::run() -> query("SELECT `name` FROM `contact` WHERE `user`=? ORDER BY `name` DESC;", [$log]);
                $contact = $querycontact -> fetchAll();

                if (count($contact) > 0) {
                    echo 'Или выберите из списка:<br />';
                    echo '<select name="uzcon">';
                    echo '<option value="0">Список контактов</option>';

                    foreach($contact as $data) {
                        echo '<option value="'.$data['name'].'">'.nickname($data['name']).'</option>';
                    }
                    echo '</select><br />';
                }

                echo '<textarea cols="25" rows="5" name="msg" id="markItUp"></textarea><br />';

                if ($udata['point'] < $config['privatprotect']) {
                    echo 'Проверочный код:<br />';
                    echo '<img src="/captcha" alt="" /><br />';
                    echo '<input name="provkod" size="6" maxlength="6" /><br />';
                }

                echo '<input value="Отправить" type="submit" /></form></div><br />';

                echo 'Введите логин или выберите пользователя из своего контакт-листа<br />';

            } else {
                if (!user_privacy($uz) || is_admin() || is_contact($uz, $log)){

                    echo '<i class="fa fa-envelope"></i> Сообщение для <b>'.profile($uz).'</b> '.user_visit($uz).':<br />';
                    echo '<i class="fa fa-history"></i> <a href="/private?act=history&amp;uz='.$uz.'">История переписки</a><br /><br />';

                    $ignorstr = DB::run() -> querySingle("SELECT `id` FROM ignoring WHERE `user`=? AND `name`=? LIMIT 1;", [$log, $uz]);
                    if (!empty($ignorstr)) {
                        echo '<b>Внимание! Данный пользователь внесен в ваш игнор-лист!</b><br />';
                    }

                    echo '<div class="form">';
                    echo '<form action="/private?act=send&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';

                    echo '<textarea cols="25" rows="5" name="msg" id="markItUp"></textarea><br />';

                    if ($udata['point'] < $config['privatprotect']) {
                        echo 'Проверочный код:<br />';
                        echo '<img src="/captcha" alt="" /><br />';
                        echo '<input name="provkod" size="6" maxlength="6" /><br />';
                    }

                    echo '<input value="Отправить" type="submit" /></form></div><br />';

                } else {
                    show_error('Включен режим приватности, писать могут только пользователи из контактов!');
                }
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Отправка сообщений                                   ##
        ############################################################################################
        case 'send':

            $uid = !empty($_GET['uid']) ? check($_GET['uid']) : 0;
            $msg = isset($_POST['msg']) ? check($_POST['msg']) : '';
            $uz = isset($_POST['uzcon']) ? check($_POST['uzcon']) : $uz;
            $provkod = isset($_POST['provkod']) ? check(strtolower($_POST['provkod'])) : '';

            if ($uid == $_SESSION['token']) {
                if (!empty($uz)) {
                    if ($uz != $log) {
                        if (!user_privacy($uz) || is_admin() || is_contact($uz, $log)){
                            if ($udata['point'] >= $config['privatprotect'] || $provkod == $_SESSION['protect']) {
                                if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
                                    $queryuser = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
                                    if (!empty($queryuser)) {
                                        $uztotal = DB::run() -> querySingle("SELECT count(*) FROM `inbox` WHERE `user`=?;", [$uz]);
                                        if ($uztotal < $config['limitmail']) {
                                            // ----------------------------- Проверка на игнор ----------------------------//
                                            $ignorstr = DB::run() -> querySingle("SELECT `id` FROM ignoring WHERE `user`=? AND `name`=? LIMIT 1;", [$uz, $log]);
                                            if (empty($ignorstr)) {
                                                if (is_flood($log)) {

                                                    $msg = antimat($msg);

                                                    DB::run() -> query("UPDATE `users` SET `newprivat`=`newprivat`+1 WHERE `login`=? LIMIT 1;", [$uz]);
                                                    DB::run() -> query("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", [$uz, $log, $msg, SITETIME]);

                                                    DB::run() -> query("INSERT INTO `outbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", [$uz, $log, $msg, SITETIME]);

                                                    DB::run() -> query("DELETE FROM `outbox` WHERE `author`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `outbox` WHERE `author`=? ORDER BY `time` DESC LIMIT ".$config['limitoutmail'].") AS del);", [$log, $log]);
                                                    save_usermail(60);

                                                    $deliveryUsers = User::where('sendprivatmail', 0)
                                                        ->where('confirmreg', 0)
                                                        ->where_gt('newprivat', 0)
                                                        ->where_lt('timelastlogin', SITETIME - 86400 * $config['sendprivatmailday'])
                                                        ->where_not_equal('subscribe', '')
                                                        ->where_not_equal('email', '')
                                                        ->order_by_asc('timelastlogin')
                                                        ->limit(App::setting('sendmailpacket'))
                                                        ->find_many();

                                                    foreach ($deliveryUsers as $user) {
                                                        sendMail($user['email'],
                                                            $user['newprivat'].' непрочитанных сообщений ('.$config['title'].')',
                                                            nl2br("Здравствуйте ".nickname($user['login'])."! \nУ вас имеются непрочитанные сообщения (".$user['newprivat']." шт.) на сайте ".$config['title']." \nПрочитать свои сообщения вы можете по адресу ".$config['home']."/private"),
                                                            ['unsubkey' => $user['subscribe']]
                                                        );

                                                        $user = DBM::run()->update('users', [
                                                            'sendprivatmail' => 1,
                                                        ], [
                                                            'login' => $user['login'],
                                                        ]);
                                                    }
                                                    notice('Ваше письмо успешно отправлено!');
                                                    redirect("/private");

                                                } else {
                                                    show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
                                                }
                                            } else {
                                                show_error('Ошибка! Вы внесены в игнор-лист получателя!');
                                            }
                                        } else {
                                            show_error('Ошибка! Ящик получателя переполнен!');
                                        }
                                    } else {
                                        show_error('Ошибка! Данного адресата не существует!');
                                    }
                                } else {
                                    show_error('Ошибка! Слишком длинное или короткое сообщение!');
                                }
                            } else {
                                show_error('Ошибка! Проверочное число не совпало с данными на картинке!');
                            }
                        } else {
                            show_error('Включен режим приватности, писать могут только пользователи из контактов!');
                        }
                    } else {
                        show_error('Ошибка! Нельзя отправлять письмо самому себе!');
                    }
                } else {
                    show_error('Ошибка! Вы не ввели логин пользователя!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private?act=submit&amp;uz='.$uz.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/private">К письмам</a><br />';
        break;

        ############################################################################################
        ##                                    Жалоба на спам                                      ##
        ############################################################################################
        case 'spam':

            $uid = check($_GET['uid']);
            $id = abs(intval($_GET['id']));

            if ($uid == $_SESSION['token']) {
                $data = DB::run() -> queryFetch("SELECT * FROM `inbox` WHERE `user`=? AND `id`=? LIMIT 1;", [$log, $id]);
                if (!empty($data)) {
                    $queryspam = DB::run() -> querySingle("SELECT `id` FROM `spam` WHERE relate=? AND `idnum`=? LIMIT 1;", [3, $id]);

                    if (empty($queryspam)) {
                        if (is_flood($log)) {
                            DB::run() -> query("INSERT INTO `spam` (relate, `idnum`, `user`, `login`, `text`, `time`, `addtime`, link) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", [3, $data['id'], $log, $data['author'], $data['text'], $data['time'], SITETIME, '']);

                            notice('Жалоба успешно отправлена!');
                            redirect("/private?page=$page");

                        } else {
                            show_error('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.flood_period().' секунд!');
                        }
                    } else {
                        show_error('Ошибка! Вы уже отправили жалобу на данное сообщение!');
                    }
                } else {
                    show_error('Ошибка! Данное сообщение адресовано не вам!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private?page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                 Удаление сообщений                                     ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);
                    $deltrash = SITETIME + 86400 * $config['expiresmail'];

                    DB::run() -> query("DELETE FROM `trash` WHERE `del`<?;", [SITETIME]);

                    DB::run() -> query("INSERT INTO `trash` (`user`, `author`, `text`, `time`, `del`) SELECT `user`, `author`, `text`, `time`, ? FROM `inbox` WHERE `id` IN (".$del.") AND `user`=?;", [$deltrash, $log]);

                    DB::run() -> query("DELETE FROM `inbox` WHERE `id` IN (".$del.") AND `user`=?;", [$log]);
                    save_usermail(60);

                    notice('Выбранные сообщения успешно удалены!');
                    redirect("/private?page=$page");

                } else {
                    show_error('Ошибка удаления! Отсутствуют выбранные сообщения');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private?page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                           Удаление отправленных сообщений                              ##
        ############################################################################################
        case 'outdel':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if ($del > 0) {
                    $del = implode(',', $del);

                    DB::run() -> query("DELETE FROM `outbox` WHERE `id` IN (".$del.") AND `author`=?;", [$log]);

                    notice('Выбранные сообщения успешно удалены!');
                    redirect("/private?act=output&page=$page");

                } else {
                    show_error('Ошибка удаления! Отсутствуют выбранные сообщения');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private?act=output&amp;page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Очистка входящих сообщений                           ##
        ############################################################################################
        case 'alldel':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (empty($udata['newprivat'])) {
                    $deltrash = SITETIME + 86400 * $config['expiresmail'];

                    DB::run() -> query("DELETE FROM `trash` WHERE `del`<?;", [SITETIME]);

                    DB::run() -> query("INSERT INTO `trash` (`user`, `author`, `text`, `time`, `del`) SELECT `user`, `author`, `text`, `time`, ? FROM `inbox` WHERE `user`=?;", [$deltrash, $log]);

                    DB::run() -> query("DELETE FROM `inbox` WHERE `user`=?;", [$log]);
                    save_usermail(60);

                    notice('Ящик успешно очищен!');
                    redirect("/private");

                } else {
                    show_error('Ошибка! У вас имеются непрочитанные сообщения!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                           Очистка отправленных сообщений                               ##
        ############################################################################################
        case 'alloutdel':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                DB::run() -> query("DELETE FROM `outbox` WHERE `author`=?;", [$log]);

                notice('Ящик успешно очищен!');
                redirect("/private?act=output");

            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private?act=output">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                              Очистка удаленных сообщений                               ##
        ############################################################################################
        case 'alltrashdel':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                DB::run() -> query("DELETE FROM `trash` WHERE `user`=?;", [$log]);

                notice('Ящик успешно очищен!');
                redirect("/private?act=trash");

            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private?act=trash">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                  Просмотр переписки                                    ##
        ############################################################################################
        case 'history':

            echo '<i class="fa fa-envelope"></i> <a href="/private">Входящие</a> / ';
            echo '<a href="/private?act=output">Отправленные</a> / ';
            echo '<a href="/private?act=trash">Корзина</a><hr />';

            if ($uz != $log) {
                $queryuser = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
                if (!empty($queryuser)) {
                    $total = DB::run() -> query("SELECT count(*) FROM `inbox` WHERE `user`=? AND `author`=? UNION ALL SELECT count(*) FROM `outbox` WHERE `user`=? AND `author`=?;", [$log, $uz, $uz, $log]);

                    $total = array_sum($total -> fetchAll(PDO::FETCH_COLUMN));
                    $page = App::paginate(App::setting('privatpost'), $total);

                    if ($total > 0) {

                        $queryhistory = DB::run() -> query("SELECT * FROM `inbox` WHERE `user`=? AND `author`=? UNION ALL SELECT * FROM `outbox` WHERE `user`=? AND `author`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['privatpost'].";", [$log, $uz, $uz, $log]);

                        while ($data = $queryhistory -> fetch()) {
                            echo '<div class="b">';
                            echo user_avatars($data['author']);
                            echo '<b>'.profile($data['author']).'</b> '.user_online($data['author']).' ('.date_fixed($data['time']).')</div>';
                            echo '<div>'.App::bbCode($data['text']).'</div>';
                        }

                        App::pagination($page);

                        if (!user_privacy($uz) || is_admin() || is_contact($uz, $log)){

                            echo '<br /><div class="form">';
                            echo '<form action="/private?act=send&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';
                            echo 'Сообщение:<br />';
                            echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';

                            if ($udata['point'] < $config['privatprotect']) {
                                echo 'Проверочный код:<br /> ';
                                echo '<img src="/captcha" alt="" /><br />';
                                echo '<input name="provkod" size="6" maxlength="6" /><br />';
                            }

                            echo '<input value="Быстрый ответ" type="submit" /></form></div><br />';

                        } else {
                            show_error('Включен режим приватности, писать могут только пользователи из контактов!');
                        }

                        echo 'Всего писем: <b>'.(int)$total.'</b><br /><br />';

                    } else {
                        show_error('История переписки отсутствует!');
                    }
                } else {
                    show_error('Ошибка! Данного адресата не существует!');
                }
            } else {
                show_error('Ошибка! Отсутствует переписка с самим собой!');
            }
        break;

    default:
        redirect("/private");
    endswitch;

} else {
    show_login('Вы не авторизованы, для просмотра писем, необходимо');
}

echo '<i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br />';
echo '<i class="fa fa-envelope"></i> <a href="/private?act=submit">Написать письмо</a><br />';
echo '<i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br />';

App::view($config['themes'].'/foot');
