<?php
App::view($config['themes'].'/index');

if (! isset($act)) {
    $act  = check(Request::input('act', 'index'));
}
$uz   = check(Request::input('uz'));
$page = abs(intval(Request::input('page', 1)));

show_title('Приватные сообщения');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $total = Inbox::where('user_id', App::getUserId())->count();
    $page = App::paginate(App::setting('privatpost'), $total);

    $totalOutbox = Outbox::where('user_id', App::getUserId())->count();
    $totalTrash = Trash::where('user_id', App::getUserId())->count();

    echo '<i class="fa fa-envelope"></i> <b>Входящие ('.$total.')</b> / ';
    echo '<a href="/private?act=output">Отправленные ('.$totalOutbox.')</a> / ';
    echo '<a href="/private?act=trash">Корзина ('.$totalTrash.')</a><hr />';

    if (App::user('newprivat') > 0) {
        echo '<div style="text-align:center"><b><span style="color:#ff0000">Получено новых писем: '.App::user('newprivat').'</span></b></div>';
        DB::run() -> query("UPDATE `users` SET `newprivat`=?, `sendprivatmail`=? WHERE `user_id`=? LIMIT 1;", [0, 0, App::getUserId()]);
    }

    if ($total >= (App::setting('limitmail') - (App::setting('limitmail') / 10)) && $total < App::setting('limitmail')) {
        echo '<div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик почти заполнен, необходимо очистить или удалить старые сообщения!</span></b></div>';
    }

    if ($total >= App::setting('limitmail')) {
        echo '<div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик переполнен, вы не сможете получать письма пока не очистите его!</span></b></div>';
    }

    if ($total > 0) {

        $messages = Inbox::where('user_id', App::getUserId())
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('author')
            ->get();

        echo '<form action="/private?act=del&amp;page='.$page['current'].'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
        echo '<div class="form">';
        echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
        echo '</div>';

       foreach($messages as $data) {

            echo '<div class="b">';
            echo '<div class="img">'.user_avatars($data->author).'</div>';
            echo '<b>'.profile($data->author).'</b>  ('.date_fixed($data['created_at']).')<br />';
            echo user_title($data->author).' '.user_online($data->author).'</div>';

            echo '<div>'.App::bbCode($data['text']).'<br />';

            echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';
            echo '<a href="/private?act=submit&amp;uz='.$data->getAuthor()->login.'">Ответить</a> / ';
            echo '<a href="/private?act=history&amp;uz='.$data->getAuthor()->login.'">История</a> / ';
            echo '<a href="/contact?act=add&amp;uz='.$data->getAuthor()->login.'&amp;token='.$_SESSION['token'].'">В контакт</a> / ';
            echo '<a href="/ignore?act=add&amp;uz='.$data->getAuthor()->login.'&amp;token='.$_SESSION['token'].'">Игнор</a> / ';

            echo '<noindex><a href="#" onclick="return sendComplaint(this)" data-type="/private" data-id="'.$data['id'].'" data-token="'.$_SESSION['token'].'" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a></noindex></div>';
        }

        echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

        App::pagination($page);

        echo 'Всего писем: <b>'.$total.'</b><br />';
        echo 'Объем ящика: <b>'.App::setting('limitmail').'</b><br /><br />';

        echo '<i class="fa fa-times"></i> <a href="/private?act=alldel&amp;token='.$_SESSION['token'].'">Очистить ящик</a><br />';
    } else {
        show_error('Входящих писем еще нет!');
    }
break;

############################################################################################
##                                 Исходящие сообщения                                    ##
############################################################################################
case 'output':

    $total = Outbox::where('user_id', App::getUserId())->count();
    $page = App::paginate(App::setting('privatpost'), $total);

    $totalInbox = Inbox::where('user_id', App::getUserId())->count();
    $totalTrash = Trash::where('user_id', App::getUserId())->count();

    echo '<i class="fa fa-envelope"></i> <a href="/private">Входящие ('.$totalInbox.')</a> / ';
    echo '<b>Отправленные ('.$total.')</b> / ';
    echo '<a href="/private?act=trash">Корзина ('.$totalTrash.')</a><hr />';

    if ($total > 0) {

        $messages = Outbox::where('user_id', App::getUserId())
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('recipient')
            ->get();

        echo '<form action="/private?act=outdel&amp;page='.$page['current'].'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
        echo '<div class="form">';
        echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
        echo '</div>';

        foreach($messages as $data) {

            echo '<div class="b">';
            echo '<div class="img">'.user_avatars($data['recipient']).'</div>';
            echo '<b>'.profile($data['recipient']).'</b>  ('.date_fixed($data['created_at']).')<br />';
            echo user_title($data['recipient']).' '.user_online($data['recipient']).'</div>';

            echo '<div>'.App::bbCode($data['text']).'<br />';

            echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';
            echo '<a href="/private?act=submit&amp;uz='.$data->getRecipient()->login.'">Написать еще</a> / ';
            echo '<a href="/private?act=history&amp;uz='.$data->getRecipient()->login.'">История</a></div>';
        }

        echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

        App::pagination($page);

        echo 'Всего писем: <b>'.$total.'</b><br />';
        echo 'Объем ящика: <b>'.App::setting('limitmail').'</b><br /><br />';

        echo '<i class="fa fa-times"></i> <a href="/private?act=alloutdel&amp;token='.$_SESSION['token'].'">Очистить ящик</a><br />';
    } else {
        show_error('Отправленных писем еще нет!');
    }
break;

############################################################################################
##                                       Корзина                                          ##
############################################################################################
case 'trash':

    $total = Trash::where('user_id', App::getUserId())->count();
    $page = App::paginate(App::setting('privatpost'), $total);

    $totalInbox = Inbox::where('user_id', App::getUserId())->count();
    $totalOutbox = Outbox::where('user_id', App::getUserId())->count();

    echo '<i class="fa fa-envelope"></i> <a href="/private">Входящие ('.$totalInbox.')</a> / ';
    echo '<a href="/private?act=output">Отправленные ('.$totalOutbox.')</a> / ';

    echo '<b>Корзина ('.$total.')</b><hr />';

    if ($total > 0) {

        $messages = Trash::where('user_id', App::getUserId())
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('author')
            ->get();

        foreach($messages as $data) {

            echo '<div class="b">';
            echo '<div class="img">'.user_avatars($data['author']).'</div>';
            echo '<b>'.profile($data['author']).'</b>  ('.date_fixed($data['time']).')<br />';
            echo user_title($data['author']).' '.user_online($data['author']).'</div>';

            echo '<div>'.App::bbCode($data['text']).'<br />';

            echo '<a href="/private?act=submit&amp;uz='.$data->getAuthor()->login.'">Ответить</a> / ';
            echo '<a href="/contact?act=add&amp;uz='.$data->getAuthor()->login.'&amp;token='.$_SESSION['token'].'">В контакт</a> / ';
            echo '<a href="/ignore?act=add&amp;uz='.$data->getAuthor()->login.'&amp;token='.$_SESSION['token'].'">Игнор</a></div>';
        }

        App::pagination($page);

        echo 'Всего писем: <b>'.$total.'</b><br />';
        echo 'Срок хранения: <b>'.App::setting('expiresmail').'</b><br /><br />';

        echo '<i class="fa fa-times"></i> <a href="/private?act=alltrashdel&amp;token='.$_SESSION['token'].'">Очистить ящик</a><br />';
    } else {
        show_error('Удаленных писем еще нет!');
    }
break;

############################################################################################
##                                   Отправка привата                                     ##
############################################################################################
case 'submit':

    $user = ! empty($uz) ? User::where('login', $uz)->first() : null;

    if ($user) {

        echo '<i class="fa fa-envelope"></i> Сообщение для <b>' . profile($user) . '</b> ' . user_visit($user) . ':<br />';
        echo '<i class="fa fa-history"></i> <a href="/private?act=history&amp;uz=' . $user->login . '">История переписки</a><br /><br />';

        echo '<div class="form">';
        echo '<form action="/private?act=send&amp;uz='.$uz.'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

        echo '<textarea cols="25" rows="5" name="msg" id="markItUp"></textarea><br />';

        if (App::user('point') < App::setting('privatprotect')) {
            echo 'Проверочный код:<br />';
            echo '<img src="/captcha" alt="" /><br />';
            echo '<input name="provkod" size="6" maxlength="6" /><br />';
        }

        echo '<input value="Отправить" type="submit" /></form></div><br />';

    } else {

        echo '<div class="form">';
        echo '<form action="/private?act=send" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

        echo 'Введите логин:<br />';
        echo '<input type="text" name="uz" maxlength="20" /><br />';

        $contacts = Contact::where('user_id', App::getUserId())
            ->rightJoin('users', 'contact.contact_id', '=', 'users.id')
            ->orderBy('users.login')
            ->get();

        if (count($contacts) > 0) {
            echo 'Или выберите из списка:<br />';
            echo '<select name="contact">';
            echo '<option value="0">Список контактов</option>';

            foreach($contacts as $data) {
                echo '<option value="'.$data->getContact()->login.'">'.$data->getContact()->login.'</option>';
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
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private">Вернуться</a><br />';
break;

############################################################################################
##                                   Отправка сообщений                                   ##
############################################################################################
case 'send':

    $token   = check(Request::input('token'));
    $msg     = check(Request::input('msg'));
    $uz      = check(Request::input('contact', $uz));
    $provkod = check(Request::input('provkod'));

    if ($token == $_SESSION['token']) {
        if (!empty($uz)) {

            $user = User::where('login', $uz)->first();
            if ($user) {
                if ($user->id != App::getUserId()) {
                    if (App::user('point') >= App::setting('privatprotect') || $provkod == $_SESSION['protect']) {
                        if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {

                            $totalInbox = Inbox::where('user_id', $user->id)->count();

                            if ($totalInbox < App::setting('limitmail')) {

                                // -------- Проверка на игнор -----------//
                                $ignoring = Ignore::where('user_id', $user->id)
                                    ->where('ignore_id', App::getUserId())
                                    ->first();

                                if ( ! $ignoring) {
                                    if (is_flood($log)) {

                                        $msg = antimat($msg);

                                        DB::run() -> query("UPDATE `users` SET `newprivat`=`newprivat`+1 WHERE `id`=? LIMIT 1;", [$user->id]);
                                        DB::run() -> query("INSERT INTO `inbox` (`user_id`, `author_id`, `text`, `created_at`) VALUES (?, ?, ?, ?);", [$user->id, App::getUserId(), $msg, SITETIME]);

                                        DB::run() -> query("INSERT INTO `outbox` (`user_id`, `recipient_id`, `text`, `created_at`) VALUES (?, ?, ?, ?);", [App::getUserId(), $user->id, $msg, SITETIME]);

                                        DB::run() -> query("DELETE FROM `outbox` WHERE `recipient_id`=? AND `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `outbox` WHERE `recipient_id`=? ORDER BY `created_at` DESC LIMIT ".App::setting('limitoutmail').") AS del);", [App::getUserId(), App::getUserId()]);
                                        save_usermail(60);

                                        $deliveryUsers = User::where('sendprivatmail', 0)
                                            ->where('confirmreg', 0)
                                            ->where('newprivat', '>', 0)
                                            ->where('timelastlogin', '<', SITETIME - 86400 * App::setting('sendprivatmailday'))
                                            ->where('subscribe', '<>', '')
                                            ->where('email', '<>', '')
                                            ->orderBy('timelastlogin')
                                            ->limit(App::setting('sendmailpacket'))
                                            ->get();

                                        foreach ($deliveryUsers as $deliveryUser) {
                                            sendMail($deliveryUser['email'],
                                                $deliveryUser['newprivat'].' непрочитанных сообщений ('.App::setting('title').')',
                                                nl2br("Здравствуйте ".$deliveryUser['login']."! \nУ вас имеются непрочитанные сообщения (".$deliveryUser['newprivat']." шт.) на сайте ".App::setting('title')." \nПрочитать свои сообщения вы можете по адресу ".App::setting('home')."/private"),
                                                ['unsubkey' => $deliveryUser['subscribe']]
                                            );

                                            $user = User::where('id', $deliveryUser->id);
                                            $user->update(['sendprivatmail' => 1]);
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
                            show_error('Ошибка! Слишком длинное или короткое сообщение!');
                        }
                    } else {
                        show_error('Ошибка! Проверочное число не совпало с данными на картинке!');
                    }
                } else {
                    show_error('Ошибка! Нельзя отправлять письмо самому себе!');
                }
            } else {
                show_error('Ошибка! Пользователь не найден!');
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
case 'complaint':

    $token = check(Request::input('token'));
    $id = abs(intval($_GET['id']));

    if ($token == $_SESSION['token']) {
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

    $token = check(Request::input('token'));
    if (isset($_POST['del'])) {
        $del = intar($_POST['del']);
    } else {
        $del = 0;
    }

    if ($token == $_SESSION['token']) {
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

    $token = check(Request::input('token'));
    if (isset($_POST['del'])) {
        $del = intar($_POST['del']);
    } else {
        $del = 0;
    }

    if ($token == $_SESSION['token']) {
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

    $token = check(Request::input('token'));

    if ($token == $_SESSION['token']) {
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

    $token = check(Request::input('token'));

    if ($token == $_SESSION['token']) {
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

    $token = check(Request::input('token'));

    if ($token == $_SESSION['token']) {
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
                    echo '<form action="/private?act=send&amp;uz='.$uz.'" method="post">';
                    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

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
