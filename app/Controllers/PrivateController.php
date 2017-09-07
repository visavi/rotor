<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\Contact;
use App\Models\Flood;
use App\Models\Ignore;
use App\Models\Inbox;
use App\Models\Outbox;
use App\Models\Trash;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class PrivateController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        if (! isUser()) {
            abort(403, 'Для просмотра писем необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Inbox::where('user_id', getUserId())->count();
        $page = paginate(setting('privatpost'), $total);

        $page['totalOutbox'] = Outbox::where('user_id', getUserId())->count();
        $page['totalTrash'] = Trash::where('user_id', getUserId())->count();

        $newprivat = user('newprivat');

        $messages = Inbox::where('user_id', getUserId())
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('author')
            ->get();


        if ($newprivat > 0) {
            DB::update("UPDATE `users` SET `newprivat`=?, `sendprivatmail`=? WHERE `id`=? LIMIT 1;", [0, 0, getUserId()]);
        }

        return view('private/index', compact('messages', 'page', 'newprivat'));
    }

    /**
     * Исходящие сообщения
     */
    public function outbox()
    {
        $total = Outbox::where('user_id', getUserId())->count();
        $page = paginate(setting('privatpost'), $total);

        $messages = Outbox::where('user_id', getUserId())
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('recipient')
            ->get();

        $page['totalInbox'] = Inbox::where('user_id', getUserId())->count();
        $page['totalTrash'] = Trash::where('user_id', getUserId())->count();

        return view('private/outbox', compact('messages', 'page'));
    }

    /**
     * Корзина
     */
    public function trash()
    {
        $total = Trash::where('user_id', getUserId())->count();
        $page = paginate(setting('privatpost'), $total);

        $page['totalInbox'] = Inbox::where('user_id', getUserId())->count();
        $page['totalOutbox'] = Outbox::where('user_id', getUserId())->count();

        $messages = Trash::where('user_id', getUserId())
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('author')
            ->get();

        return view('private/trash', compact('messages', 'page'));
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
                ->addRule('equal', [Flood::isFlood(), true], 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' сек!');

            if ($user) {

                $validation->addRule('not_equal', [$user->id, getUserId()], ['user' => 'Нельзя отправлять письмо самому себе!']);

                if (user('point') < setting('privatprotect') && $provkod != $_SESSION['protect']) {
                    $validation->addError(['provkod' => 'Проверочное число не совпало с данными на картинке!']);
                }

                // лимит ящика
                $totalInbox = Inbox::where('user_id', $user->id)->count();
                $validation->addRule('min', [$totalInbox, setting('limitmail')], 'Ящик получателя переполнен!');

                // Проверка на игнор
                $ignoring = Ignore::where('user_id', $user->id)
                    ->where('ignore_id', getUserId())
                    ->first();

                $validation->addRule('not_equal', [$ignoring, false], ['user' => 'Вы внесены в игнор-лист получателя!']);
            }

            if ($validation->run()) {

                $msg = antimat($msg);

                DB::update("UPDATE `users` SET `newprivat`=`newprivat`+1 WHERE `id`=? LIMIT 1;", [$user->id]);
                DB::insert("INSERT INTO `inbox` (`user_id`, `author_id`, `text`, `created_at`) VALUES (?, ?, ?, ?);", [$user->id, getUserId(), $msg, SITETIME]);

                DB::insert("INSERT INTO `outbox` (`user_id`, `recipient_id`, `text`, `created_at`) VALUES (?, ?, ?, ?);", [getUserId(), $user->id, $msg, SITETIME]);

                DB::delete("DELETE FROM `outbox` WHERE `recipient_id`=? AND `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `outbox` WHERE `recipient_id`=? ORDER BY `created_at` DESC LIMIT " . setting('limitoutmail') . ") AS del);", [getUserId(), getUserId()]);
                saveUserMail(60);

                $deliveryUsers = User::where('sendprivatmail', 0)
                    ->where('confirmreg', 0)
                    ->where('newprivat', '>', 0)
                    ->where('timelastlogin', '<', SITETIME - 86400 * setting('sendprivatmailday'))
                    ->where('subscribe', '<>', '')
                    ->where('email', '<>', '')
                    ->orderBy('timelastlogin')
                    ->limit(setting('sendmailpacket'))
                    ->get();

                foreach ($deliveryUsers as $deliveryUser) {

                    $subject = $deliveryUser['newprivat'] . ' непрочитанных сообщений (' . setting('title') . ')';
                    $message = 'Здравствуйте ' . $deliveryUser['login'] . '!<br>У вас имеются непрочитанные сообщения (' . $deliveryUser['newprivat'] . ' шт.) на сайте ' . setting('title') . '<br>Прочитать свои сообщения вы можете по адресу <a href="' . siteLink(setting('home')) . '/private">' . siteLink(setting('home')) . '/private</a>';
                    $body = view('mailer.default', compact('subject', 'message'), true);
                    sendMail($deliveryUser['email'], $subject, $body, ['subscribe' => $deliveryUser['subscribe']]);

                    $user = User::where('id', $deliveryUser->id);
                    $user->update(['sendprivatmail' => 1]);
                }

                setFlash('success', 'Ваше письмо успешно отправлено!');
                redirect('/private');

            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        $contacts = Contact::where('user_id', getUserId())
            ->rightJoin('users', 'contact.contact_id', '=', 'users.id')
            ->orderBy('users.login')
            ->get();

        return view('private/send', compact('user', 'contacts'));
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
                DB::delete("DELETE FROM `outbox` WHERE `id` IN (" . $del . ") AND `user_id`=?;", [getUserId()]);
            } else {
                $deltrash = SITETIME + 86400 * setting('expiresmail');

                DB::delete("DELETE FROM `trash` WHERE `deleted_at`<?;", [SITETIME]);

                DB::insert("INSERT INTO `trash` (`user_id`, `author_id`, `text`, `created_at`, `deleted_at`) SELECT `user_id`, `author_id`, `text`, `created_at`, ? FROM `inbox` WHERE `id` IN (" . $del . ") AND `user_id`=?;", [$deltrash, getUserId()]);

                DB::delete("DELETE FROM `inbox` WHERE `id` IN (" . $del . ") AND `user_id`=?;", [getUserId()]);
                saveUserMail(60);
            }

            setFlash('success', 'Выбранные сообщения успешно удалены!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        $type = $type ? '/' . $type : '';
        redirect('/private' . $type . '?page=' . $page);
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
            ->addRule('empty', user('newprivat'), 'У вас имеются непрочитанные сообщения!');

        if ($validation->run()) {

            if ($type == 'outbox') {
                DB::delete("DELETE FROM `outbox` WHERE `user_id`=?;", [getUserId()]);
            } elseif ($type == 'trash') {
                DB::delete("DELETE FROM `trash` WHERE `user_id`=?;", [getUserId()]);
            } else {
                $deltrash = SITETIME + 86400 * setting('expiresmail');

                DB::delete("DELETE FROM `trash` WHERE `deleted_at`<?;", [SITETIME]);

                DB::insert("INSERT INTO `trash` (`user_id`, `author_id`, `text`, `created_at`, `deleted_at`) SELECT `user_id`, `author_id`, `text`, `created_at`, ? FROM `inbox` WHERE `user_id`=?;", [$deltrash, getUserId()]);

                DB::delete("DELETE FROM `inbox` WHERE `user_id`=?;", [getUserId()]);
                saveUserMail(60);
            }

            setFlash('success', 'Ящик успешно очищен!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        $type = $type ? '/' . $type : '';
        redirect('/private' . $type . '?page=' . $page);
    }

    /**
     * Просмотр переписки
     */
    public function history()
    {
        $login = check(Request::input('user'));

        if (! $user = getUserByLogin($login)) {
            abort('default', 'Пользователя с данным логином не существует!');
        }

        if ($user->id == getUserId()) {
            abort('default', 'Отсутствует переписка с самим собой!');
        }

        $totalInbox = Inbox::where('user_id', getUserId())->where('author_id', $user->id)->count();
        $totalOutbox = Outbox::where('user_id', getUserId())->where('recipient_id', $user->id)->count();

        $total = $totalInbox + $totalOutbox;

        $page = paginate(setting('privatpost'), $total);

        $outbox = Outbox::select('id', 'user_id', 'user_id as author_id', 'text', 'created_at')
            ->where('user_id', getUserId())
            ->where('recipient_id', $user->id);

        $messages = Inbox::where('user_id', getUserId())
            ->where('author_id', $user->id)
            ->unionAll($outbox)
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('author')
            ->get();

        return view('private/history', compact('messages', 'page', 'user'));
    }
}
