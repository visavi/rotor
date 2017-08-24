<?php

class PrivateController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        if (!is_user()) {
            App::abort(403, 'Для просмотра писем необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Inbox::where('user_id', App::getUserId())->count();
        $page = App::paginate(Setting::get('privatpost'), $total);

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
            DB::run()->query("UPDATE `users` SET `newprivat`=?, `sendprivatmail`=? WHERE `id`=? LIMIT 1;", [0, 0, App::getUserId()]);
        }

        App::view('private/index', compact('messages', 'page', 'newprivat'));
    }

    /**
     * Исходящие сообщения
     */
    public function outbox()
    {
        $total = Outbox::where('user_id', App::getUserId())->count();
        $page = App::paginate(Setting::get('privatpost'), $total);

        $messages = Outbox::where('user_id', App::getUserId())
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('recipient')
            ->get();

        $page['totalInbox'] = Inbox::where('user_id', App::getUserId())->count();
        $page['totalTrash'] = Trash::where('user_id', App::getUserId())->count();

        App::view('private/outbox', compact('messages', 'page'));
    }

    /**
     * Корзина
     */
    public function trash()
    {
        $total = Trash::where('user_id', App::getUserId())->count();
        $page = App::paginate(Setting::get('privatpost'), $total);

        $page['totalInbox'] = Inbox::where('user_id', App::getUserId())->count();
        $page['totalOutbox'] = Outbox::where('user_id', App::getUserId())->count();

        $messages = Trash::where('user_id', App::getUserId())
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('author')
            ->get();

        App::view('private/trash', compact('messages', 'page'));
    }

    /**
     * Отправка сообщений
     */
    public function send()
    {
        $login = check(Request::input('user'));

        if (! empty(Request::input('contact'))) {
            $login = check(Request::input('contact'));
        }

        $user = User::where('login', $login)->first();

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg = check(Request::input('msg'));
            $provkod = check(Request::input('provkod'));

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->addRule('bool', $user, ['user' => 'Ошибка! Пользователь не найден!'])
                ->addRule('string', $msg, ['msg' => 'Ошибка! Слишком длинное или короткое сообщение!'], true, 5, 1000)
                ->addRule('equal', [Flood::isFlood(), true], 'Антифлуд! Разрешается публиковать события раз в ' . Flood::getPeriod() . ' сек!');

            if ($user) {

                $validation->addRule('not_equal', [$user->id, App::getUserId()], ['user' => 'Нельзя отправлять письмо самому себе!']);

                if (App::user('point') < Setting::get('privatprotect') && $provkod != $_SESSION['protect']) {
                    $validation->addError(['provkod' => 'Проверочное число не совпало с данными на картинке!']);
                }

                // лимит ящика
                $totalInbox = Inbox::where('user_id', $user->id)->count();
                $validation->addRule('min', [$totalInbox, Setting::get('limitmail')], 'Ящик получателя переполнен!');

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

                DB::run()->query("DELETE FROM `outbox` WHERE `recipient_id`=? AND `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `outbox` WHERE `recipient_id`=? ORDER BY `created_at` DESC LIMIT " . Setting::get('limitoutmail') . ") AS del);", [App::getUserId(), App::getUserId()]);
                save_usermail(60);

                $deliveryUsers = User::where('sendprivatmail', 0)
                    ->where('confirmreg', 0)
                    ->where('newprivat', '>', 0)
                    ->where('timelastlogin', '<', SITETIME - 86400 * Setting::get('sendprivatmailday'))
                    ->where('subscribe', '<>', '')
                    ->where('email', '<>', '')
                    ->orderBy('timelastlogin')
                    ->limit(Setting::get('sendmailpacket'))
                    ->get();

                $sitelink = starts_with(Setting::get('home'), '//') ? 'http:' . Setting::get('home') : Setting::get('home');

                foreach ($deliveryUsers as $deliveryUser) {

                    $subject = $deliveryUser['newprivat'] . ' непрочитанных сообщений (' . Setting::get('title') . ')';
                    $message = 'Здравствуйте ' . $deliveryUser['login'] . '!<br>У вас имеются непрочитанные сообщения (' . $deliveryUser['newprivat'] . ' шт.) на сайте ' . Setting::get('title') . '<br>Прочитать свои сообщения вы можете по адресу <a href="' . $sitelink . '/private">' . $sitelink . '/private</a>';
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

        $contacts = Contact::where('user_id', App::getUserId())
            ->rightJoin('users', 'contact.contact_id', '=', 'users.id')
            ->orderBy('users.login')
            ->get();

        App::view('private/send', compact('user', 'contacts'));
    }

    /**
     * Удаление сообщений
     */
    public function delete()
    {
        $token = check(Request::input('token'));
        $type  = check(Request::input('type'));
        $del   = intar(Request::input('del'));
        $page  = abs(intval(Request::input('page', 1)));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', $del, 'Ошибка удаления! Отсутствуют выбранные сообщения');

        if ($validation->run()) {

            $del = implode(',', $del);

            if ($type == 'outbox') {
                DB::run()->query("DELETE FROM `outbox` WHERE `id` IN (" . $del . ") AND `user_id`=?;", [App::getUserId()]);
            } else {
                $deltrash = SITETIME + 86400 * Setting::get('expiresmail');

                DB::run()->query("DELETE FROM `trash` WHERE `deleted_at`<?;", [SITETIME]);

                DB::run()->query("INSERT INTO `trash` (`user_id`, `author_id`, `text`, `created_at`, `deleted_at`) SELECT `user_id`, `author_id`, `text`, `created_at`, ? FROM `inbox` WHERE `id` IN (" . $del . ") AND `user_id`=?;", [$deltrash, App::getUserId()]);

                DB::run()->query("DELETE FROM `inbox` WHERE `id` IN (" . $del . ") AND `user_id`=?;", [App::getUserId()]);
                save_usermail(60);
            }

            App::setFlash('success', 'Выбранные сообщения успешно удалены!');
        } else {
            App::setFlash('danger', $validation->getErrors());
        }

        $type = $type ? '/' . $type : '';
        App::redirect('/private' . $type . '?page=' . $page);
    }

    /**
     * Очистка сообщений
     */
    public function clear()
    {
        $token = check(Request::input('token'));
        $type  = check(Request::input('type'));
        $page  = abs(intval(Request::input('page', 1)));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('empty', App::user('newprivat'), 'У вас имеются непрочитанные сообщения!');

        if ($validation->run()) {

            if ($type == 'outbox') {
                DB::run()->query("DELETE FROM `outbox` WHERE `user_id`=?;", [App::getUserId()]);
            } elseif ($type == 'trash') {
                DB::run()->query("DELETE FROM `trash` WHERE `user_id`=?;", [App::getUserId()]);
            } else {
                $deltrash = SITETIME + 86400 * Setting::get('expiresmail');

                DB::run()->query("DELETE FROM `trash` WHERE `deleted_at`<?;", [SITETIME]);

                DB::run()->query("INSERT INTO `trash` (`user_id`, `author_id`, `text`, `created_at`, `deleted_at`) SELECT `user_id`, `author_id`, `text`, `created_at`, ? FROM `inbox` WHERE `user_id`=?;", [$deltrash, App::getUserId()]);

                DB::run()->query("DELETE FROM `inbox` WHERE `user_id`=?;", [App::getUserId()]);
                save_usermail(60);
            }

            App::setFlash('success', 'Ящик успешно очищен!');
        } else {
            App::setFlash('danger', $validation->getErrors());
        }

        $type = $type ? '/' . $type : '';
        App::redirect('/private/' . $type . '?page=' . $page);
    }

    /**
     * Просмотр переписки
     */
    public function history()
    {
        $login = check(Request::input('user'));

        if (!$user = user($login)) {
            App::abort('default', 'Пользователя с данным логином не существует!');
        }

        if ($user->id == App::getUserId()) {
            App::abort('default', 'Отсутствует переписка с самим собой!');
        }

        $totalInbox = Inbox::where('user_id', App::getUserId())->where('author_id', $user->id)->count();
        $totalOutbox = Outbox::where('user_id', App::getUserId())->where('recipient_id', $user->id)->count();

        $total = $totalInbox + $totalOutbox;

        $page = App::paginate(Setting::get('privatpost'), $total);

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
    }
}
