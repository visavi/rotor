<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\Contact;
use App\Models\Flood;
use App\Models\Ignore;
use App\Models\Inbox;
use App\Models\Outbox;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class PrivateController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

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
        $page  = paginate(setting('privatpost'), $total);

        $page['totalOutbox'] = Outbox::where('user_id', getUserId())->count();

        $messages = Inbox::where('user_id', getUserId())
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('author')
            ->get();

        $newprivat = user('newprivat');

        if ($newprivat) {
            $user = User::find(getUserId());
            $user->update([
                'newprivat'      => 0,
                'sendprivatmail' => 0,
            ]);
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

        return view('private/outbox', compact('messages', 'page'));
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

            $token   = check(Request::input('token'));
            $msg     = check(Request::input('msg'));
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

                $validation->addRule('empty', $ignoring, ['user' => 'Вы внесены в игнор-лист получателя!']);
            }

            if ($validation->run()) {

                $msg = antimat($msg);

                $user->increment('newprivat');

                Inbox::create([
                    'user_id'    => $user->id,
                    'author_id'  => getUserId(),
                    'text'       => $msg,
                    'created_at' => SITETIME,
                ]);

                Outbox::create([
                    'user_id'       => getUserId(),
                    'recipient_id'  => $user->id,
                    'text'          => $msg,
                    'created_at'    => SITETIME,
                ]);

                DB::delete("DELETE FROM `outbox` WHERE `recipient_id`=? AND `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `outbox` WHERE `recipient_id`=? ORDER BY `created_at` DESC LIMIT " . setting('limitoutmail') . ") AS del);", [getUserId(), getUserId()]);
                saveUserMail(60);

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

            if ($type == 'outbox') {
                Outbox::where('user_id', getUserId())
                    ->whereIn('id', $del)
                    ->delete();
            } else {
                Inbox::where('user_id', getUserId())
                    ->whereIn('id', $del)
                    ->delete();
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
                Outbox::where('user_id', getUserId())->delete();
            } else {
                Inbox::where('user_id', getUserId())->delete();
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

        $totalInbox  = Inbox::where('user_id', getUserId())->where('author_id', $user->id)->count();
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
