<?php

$uz   = check(Request::input('uz'));
$page = abs(intval(Request::input('page', 1)));

if (! is_user()) App::abort(403);

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $total = Inbox::where('user_id', App::getUserId())->count();
    $page = App::paginate(App::setting('privatpost'), $total);

    $page['totalOutbox'] = Outbox::where('user_id', App::getUserId())->count();
    $page['totalTrash'] = Trash::where('user_id', App::getUserId())->count();

    $newprivat = App::user('newprivat');

    $messages = Inbox::where('user_id', App::getUserId())
        ->orderBy('created_at', 'desc')
        ->offset($page['offset'])
        ->limit($page['limit'])
        ->with('author')
        ->get();


    if ($newprivat > 0) {
        DB::run() -> query("UPDATE `users` SET `newprivat`=?, `sendprivatmail`=? WHERE `id`=? LIMIT 1;", [0, 0, App::getUserId()]);
    }

    App::view('private/index', compact('messages', 'page', 'newprivat'));
break;

############################################################################################
##                                 Исходящие сообщения                                    ##
############################################################################################
case 'output':

    $total = Outbox::where('user_id', App::getUserId())->count();
    $page = App::paginate(App::setting('privatpost'), $total);

    $messages = Outbox::where('user_id', App::getUserId())
        ->orderBy('created_at', 'desc')
        ->offset($page['offset'])
        ->limit($page['limit'])
        ->with('recipient')
        ->get();

    $page['totalInbox'] = Inbox::where('user_id', App::getUserId())->count();
    $page['totalTrash'] = Trash::where('user_id', App::getUserId())->count();

    App::view('private/output', compact('messages', 'page'));
break;

############################################################################################
##                                       Корзина                                          ##
############################################################################################
case 'trash':

    $total = Trash::where('user_id', App::getUserId())->count();
    $page = App::paginate(App::setting('privatpost'), $total);

    $page['totalInbox'] = Inbox::where('user_id', App::getUserId())->count();
    $page['totalOutbox'] = Outbox::where('user_id', App::getUserId())->count();

    $messages = Trash::where('user_id', App::getUserId())
        ->orderBy('created_at', 'desc')
        ->offset($page['offset'])
        ->limit($page['limit'])
        ->with('author')
        ->get();

    App::view('private/trash', compact('messages', 'page'));

break;

############################################################################################
##                                   Отправка сообщений                                   ##
############################################################################################
case 'send':

    $login = check(Request::input('user'));

    if (! empty(Request::input('contact'))) {
        $login = check(Request::input('contact'));
    }

    $user = ! empty($user) ? User::where('login', $login)->first() : null;

    if (Request::isMethod('post')) {

        $token = check(Request::input('token'));
        $msg = check(Request::input('msg'));
        $provkod = check(Request::input('provkod'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('bool', $user, ['user' => 'Ошибка! Пользователь не найден!'])
            ->addRule('string', $msg, ['msg' => 'Ошибка! Слишком длинное или короткое сообщение!'], true, 5, 1000)
            ->addRule('equal', [is_flood(App::getUsername()), true], 'Антифлуд! Разрешается публиковать события раз в '.flood_period().' сек!');

        if ($user) {

            $validation->addRule('not_equal', [$user->id, App::getUserId()], ['user' => 'Нельзя отправлять письмо самому себе!']);

            if (App::user('point') < App::setting('privatprotect') && $provkod != $_SESSION['protect']) {
                $validation -> addError(['provkod' => 'Проверочное число не совпало с данными на картинке!']);
            }

            // лимит ящика
            $totalInbox = Inbox::where('user_id', $user->id)->count();
            $validation->addRule('min', [$totalInbox, App::setting('limitmail')], 'Ящик получателя переполнен!');

            // Проверка на игнор
            $ignoring = Ignore::where('user_id', $user->id)
                ->where('ignore_id', App::getUserId())
                ->first();

            $validation->addRule('not_equal', [$ignoring, false], ['user' => 'Вы внесены в игнор-лист получателя!']);
        }

        if ($validation->run()) {

            $msg = antimat($msg);

            DB::run()->query("UPDATE `users` SET `newprivat`=`newprivat`+1 WHERE `id`=? LIMIT 1;", [$user->id]);
            DB::run()->query("INSERT INTO `inbox` (`user_id`, `author_id`, `text`, `created_at`) VALUES (?, ?, ?, ?);", [$user->id, App::getUserId(), $msg, SITETIME]);

            DB::run()->query("INSERT INTO `outbox` (`user_id`, `recipient_id`, `text`, `created_at`) VALUES (?, ?, ?, ?);", [App::getUserId(), $user->id, $msg, SITETIME]);

            DB::run()->query("DELETE FROM `outbox` WHERE `recipient_id`=? AND `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `outbox` WHERE `recipient_id`=? ORDER BY `created_at` DESC LIMIT " . App::setting('limitoutmail') . ") AS del);", [App::getUserId(), App::getUserId()]);
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
                    $deliveryUser['newprivat'] . ' непрочитанных сообщений (' . App::setting('title') . ')',
                    nl2br("Здравствуйте " . $deliveryUser['login'] . "! \nУ вас имеются непрочитанные сообщения (" . $deliveryUser['newprivat'] . " шт.) на сайте " . App::setting('title') . " \nПрочитать свои сообщения вы можете по адресу " . App::setting('home') . "/private"),
                    ['unsubkey' => $deliveryUser['subscribe']]
                );

                $user = User::where('id', $deliveryUser->id);
                $user->update(['sendprivatmail' => 1]);
            }

            App::setFlash('success', 'Ваше письмо успешно отправлено!');
            App::redirect('/private');

        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    App::view('private/send', compact('user'));
break;

############################################################################################
##                                    Жалоба на спам                                      ##
############################################################################################
case 'complaint':

    if (! Request::ajax()) App::redirect('/');

    $token = check(Request::input('token'));
    $page = abs(intval(Request::input('page')));
    $id = abs(intval(Request::input('id')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('bool', is_user(), 'Для отправки жалобы необходимо авторизоваться');

    $data = Inbox::find($id);
    $validation->addRule('custom', $data, 'Выбранное вами сообщение для жалобы не существует!');

    $spam = Spam::where(['relate_type' => Inbox::class, 'relate_id' => $id])->first();
    $validation->addRule('custom', !$spam, 'Жалоба на данное сообщение уже отправлена!');

    if ($validation->run()) {

        $spam = new Spam();
        $spam->relate_type = Inbox::class;
        $spam->relate_id   = $data['id'];
        $spam->user_id     = App::getUserId();
        $spam->link        = '';
        $spam->created_at  = SITETIME;
        $spam->save();

        exit(json_encode(['status' => 'success']));
    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
break;

/* Удаление сообщений */
case 'delete':


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
            $deltrash = SITETIME + 86400 * App::setting('expiresmail');

            DB::run() -> query("DELETE FROM `trash` WHERE `del`<?;", [SITETIME]);

            DB::run() -> query("INSERT INTO `trash` (`user`, `author`, `text`, `time`, `del`) SELECT `user`, `author`, `text`, `time`, ? FROM `inbox` WHERE `id` IN (".$del.") AND `user`=?;", [$deltrash, App::getUsername()]);

            DB::run() -> query("DELETE FROM `inbox` WHERE `id` IN (".$del.") AND `user`=?;", [App::getUsername()]);
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

            DB::run() -> query("DELETE FROM `outbox` WHERE `id` IN (".$del.") AND `author`=?;", [App::getUsername()]);

            notice('Выбранные сообщения успешно удалены!');
            redirect("/private?act=output&page=$page");

        } else {
            show_error('Ошибка удаления! Отсутствуют выбранные сообщения');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private/output?page='.$page.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Очистка входящих сообщений                           ##
############################################################################################
case 'alldel':

    $token = check(Request::input('token'));

    if ($token == $_SESSION['token']) {
        if (empty(App::user('newprivat'))) {
            $deltrash = SITETIME + 86400 * App::setting('expiresmail');

            DB::run() -> query("DELETE FROM `trash` WHERE `del`<?;", [SITETIME]);

            DB::run() -> query("INSERT INTO `trash` (`user`, `author`, `text`, `time`, `del`) SELECT `user`, `author`, `text`, `time`, ? FROM `inbox` WHERE `user`=?;", [$deltrash, App::getUsername()]);

            DB::run() -> query("DELETE FROM `inbox` WHERE `user`=?;", [App::getUsername()]);
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
        DB::run() -> query("DELETE FROM `outbox` WHERE `author`=?;", [App::getUsername()]);

        notice('Ящик успешно очищен!');
        redirect("/private?act=output");

    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private/output">Вернуться</a><br />';
break;

############################################################################################
##                              Очистка удаленных сообщений                               ##
############################################################################################
case 'alltrashdel':

    $token = check(Request::input('token'));

    if ($token == $_SESSION['token']) {
        DB::run() -> query("DELETE FROM `trash` WHERE `user`=?;", [App::getUsername()]);

        notice('Ящик успешно очищен!');
        redirect("/private?act=trash");

    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/private/trash">Вернуться</a><br />';
break;

############################################################################################
##                                  Просмотр переписки                                    ##
############################################################################################
case 'history':

    if ($uz != App::getUsername()) {
        $queryuser = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
        if (!empty($queryuser)) {
            $total = DB::run() -> query("SELECT count(*) FROM `inbox` WHERE `user`=? AND `author`=? UNION ALL SELECT count(*) FROM `outbox` WHERE `user`=? AND `author`=?;", [App::getUsername(), $uz, $uz, App::getUsername()]);

            $total = array_sum($total -> fetchAll(PDO::FETCH_COLUMN));
            $page = App::paginate(App::setting('privatpost'), $total);

            if ($total > 0) {

                $queryhistory = DB::run() -> query("SELECT * FROM `inbox` WHERE `user`=? AND `author`=? UNION ALL SELECT * FROM `outbox` WHERE `user`=? AND `author`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".App::setting('privatpost').";", [App::getUsername(), $uz, $uz, App::getUsername()]);

                while ($data = $queryhistory -> fetch()) {
                    echo '<div class="b">';
                    echo user_avatars($data['author']);
                    echo '<b>'.profile($data['author']).'</b> '.user_online($data['author']).' ('.date_fixed($data['time']).')</div>';
                    echo '<div>'.App::bbCode($data['text']).'</div>';
                }

                App::pagination($page);

                if (!user_privacy($uz) || is_admin() || is_contact($uz, App::getUsername())){

                    echo '<br /><div class="form">';
                    echo '<form action="/private?act=send&amp;uz='.$uz.'" method="post">';
                    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

                    echo 'Сообщение:<br />';
                    echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';

                    if (App::user('point') < App::setting('privatprotect')) {
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

    App::view('private/history', compact('messages'));
break;

endswitch;
