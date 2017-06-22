<?php

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
case 'outbox':

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

    App::view('private/outbox', compact('messages', 'page'));
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

            $sitelink = starts_with(App::setting('home'), '//') ? 'http:'. App::setting('home') : App::setting('home');

            foreach ($deliveryUsers as $deliveryUser) {

                $subject = $deliveryUser['newprivat'] . ' непрочитанных сообщений (' . App::setting('title') . ')';
                $message = 'Здравствуйте ' . $deliveryUser['login'] . '!<br />У вас имеются непрочитанные сообщения (' . $deliveryUser['newprivat'] . ' шт.) на сайте ' . App::setting('title') . '<br />Прочитать свои сообщения вы можете по адресу <a href="' . $sitelink . '/private">' . $sitelink . '/private</a>';
                $body = App::view('mailer.default', compact('subject', 'message'), true);
                App::sendMail($deliveryUser['email'], $subject, $body, ['subscribe' => $deliveryUser['subscribe']]);

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
    $token = check(Request::input('token'));
    $type = check(Request::input('type'));
    $del = intar(Request::input('del'));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('bool', $del, 'Ошибка удаления! Отсутствуют выбранные сообщения');

    if ($validation->run()) {

        $del = implode(',', $del);

        if ($type == 'outbox') {
            DB::run() -> query("DELETE FROM `outbox` WHERE `id` IN (".$del.") AND `user_id`=?;", [App::getUserId()]);
        } else {
            $deltrash = SITETIME + 86400 * App::setting('expiresmail');

            DB::run() -> query("DELETE FROM `trash` WHERE `deleted_at`<?;", [SITETIME]);

            DB::run() -> query("INSERT INTO `trash` (`user_id`, `author_id`, `text`, `created_at`, `deleted_at`) SELECT `user_id`, `author_id`, `text`, `created_at`, ? FROM `inbox` WHERE `id` IN (".$del.") AND `user_id`=?;", [$deltrash, App::getUserId()]);

            DB::run() -> query("DELETE FROM `inbox` WHERE `id` IN (".$del.") AND `user_id`=?;", [App::getUserId()]);
            save_usermail(60);
        }

        App::setFlash('success', 'Выбранные сообщения успешно удалены!');
    } else {
        App::setFlash('danger', $validation->getErrors());
    }

    $type = $type ? '/'.$type : '';
    App::redirect('/private'.$type.'?page='.$page);
break;

/* Очистка сообщений */
case 'clear':
    $token = check(Request::input('token'));
    $type = check(Request::input('type'));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('empty', App::user('newprivat'), 'У вас имеются непрочитанные сообщения!');

    if ($validation->run()) {

        if ($type == 'outbox') {
            DB::run() -> query("DELETE FROM `outbox` WHERE `user_id`=?;", [App::getUserId()]);
        } elseif ($type == 'trash') {
            DB::run()->query("DELETE FROM `trash` WHERE `user_id`=?;", [App::getUserId()]);
        } else {
            $deltrash = SITETIME + 86400 * App::setting('expiresmail');

            DB::run() -> query("DELETE FROM `trash` WHERE `deleted_at`<?;", [SITETIME]);

            DB::run() -> query("INSERT INTO `trash` (`user_id`, `author_id`, `text`, `created_at`, `deleted_at`) SELECT `user_id`, `author_id`, `text`, `created_at`, ? FROM `inbox` WHERE `user_id`=?;", [$deltrash, App::getUserId()]);

            DB::run() -> query("DELETE FROM `inbox` WHERE `user_id`=?;", [App::getUserId()]);
            save_usermail(60);
        }

        App::setFlash('success', 'Ящик успешно очищен!');
    } else {
        App::setFlash('danger', $validation->getErrors());
    }

    $type = $type ? '/'.$type : '';
    App::redirect('/private/'.$type.'?page='.$page);
break;

############################################################################################
##                                  Просмотр переписки                                    ##
############################################################################################
case 'history':

    $login = check(Request::input('user'));

    if (! $user = user($login)) {
        App::abort('default', 'Пользователя с данным логином не существует!');
    }

    if ($user->id == App::getUserId()) {
        App::abort('default', 'Отсутствует переписка с самим собой!');
    }

    $totalInbox = Inbox::where('user_id', App::getUserId())->where('author_id', $user->id)->count();
    $totalOutbox = Outbox::where('user_id', App::getUserId())->where('recipient_id', $user->id)->count();

    $total = $totalInbox + $totalOutbox;

    $page = App::paginate(App::setting('privatpost'), $total);

    $outbox = Outbox::select('id', 'user_id', 'user_id as author_id', 'text', 'created_at')
        ->where('user_id', App::getUserId())
        ->where('recipient_id', $user->id);

    $messages = Inbox::where('user_id', App::getUserId())
        ->where('author_id', $user->id)
        ->unionAll($outbox)
        ->orderBy('created_at', 'desc')
        ->offset($page['offset'])
        ->limit($page['limit'])
        ->with('author')
        ->get();

    App::view('private/history', compact('messages', 'page', 'user'));
break;

endswitch;
