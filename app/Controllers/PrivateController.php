<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
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

        if (! getUser()) {
            abort(403, 'Для просмотра писем необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Inbox::query()->where('user_id', getUser('id'))->count();
        $page  = paginate(setting('privatpost'), $total);

        $page['totalOutbox'] = Outbox::query()->where('user_id', getUser('id'))->count();

        $messages = Inbox::query()
            ->where('user_id', getUser('id'))
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('author')
            ->get();

        $newprivat = getUser('newprivat');

        if ($newprivat) {
            $user = User::query()->find(getUser('id'));
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
        $total = Outbox::query()->where('user_id', getUser('id'))->count();
        $page = paginate(setting('privatpost'), $total);

        $messages = Outbox::query()
            ->where('user_id', getUser('id'))
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('recipient')
            ->get();

        $page['totalInbox'] = Inbox::query()->where('user_id', getUser('id'))->count();

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

        $user = User::query()->where('login', $login)->first();

        if (Request::isMethod('post')) {

            $token   = check(Request::input('token'));
            $msg     = check(Request::input('msg'));
            $protect = check(Request::input('protect'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->true($user, ['user' => 'Ошибка! Пользователь не найден!'])
                ->length($msg, 5, 1000, ['msg' => 'Ошибка! Слишком длинное или короткое сообщение!'])
                ->equal(Flood::isFlood(), true, 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' сек!');

            if ($user) {

                $validator->notEqual($user->id, getUser('id'), ['user' => 'Нельзя отправлять письмо самому себе!']);

                if (getUser('point') < setting('privatprotect') && $protect != $_SESSION['protect']) {
                    $validator->addError(['protect' => 'Проверочное число не совпало с данными на картинке!']);
                }

                // лимит ящика
                $totalInbox = Inbox::query()->where('user_id', $user->id)->count();
                $validator->lte($totalInbox, setting('limitmail'), 'Ящик получателя переполнен!');

                // Проверка на игнор
                $ignoring = Ignore::query()
                    ->where('user_id', $user->id)
                    ->where('ignore_id', getUser('id'))
                    ->first();

                $validator->empty($ignoring, ['user' => 'Вы внесены в игнор-лист получателя!']);
            }

            if ($validator->isValid()) {

                $msg = antimat($msg);

                $user->increment('newprivat');

                Inbox::query()->create([
                    'user_id'    => $user->id,
                    'author_id'  => getUser('id'),
                    'text'       => $msg,
                    'created_at' => SITETIME,
                ]);

                Outbox::query()->create([
                    'user_id'       => getUser('id'),
                    'recipient_id'  => $user->id,
                    'text'          => $msg,
                    'created_at'    => SITETIME,
                ]);

                DB::delete("DELETE FROM `outbox` WHERE `recipient_id`=? AND `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `outbox` WHERE `recipient_id`=? ORDER BY `created_at` DESC LIMIT " . setting('limitoutmail') . ") AS del);", [getUser('id'), getUser('id')]);

                setFlash('success', 'Ваше письмо успешно отправлено!');
                redirect('/private');

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $contacts = Contact::query()
            ->where('user_id', getUser('id'))
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
        $page  = int(Request::input('page', 1));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Ошибка удаления! Отсутствуют выбранные сообщения!');

        if ($validator->isValid()) {

            if ($type == 'outbox') {
                Outbox::query()
                    ->where('user_id', getUser('id'))
                    ->whereIn('id', $del)
                    ->delete();
            } else {
                Inbox::query()
                    ->where('user_id', getUser('id'))
                    ->whereIn('id', $del)
                    ->delete();
            }

            setFlash('success', 'Выбранные сообщения успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
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
        $page  = int(Request::input('page', 1));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->empty(getUser('newprivat'), 'У вас имеются непрочитанные сообщения!');

        if ($validator->isValid()) {

            if ($type == 'outbox') {
                Outbox::query()->where('user_id', getUser('id'))->delete();
            } else {
                Inbox::query()->where('user_id', getUser('id'))->delete();
            }

            setFlash('success', 'Ящик успешно очищен!');
        } else {
            setFlash('danger', $validator->getErrors());
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

        if ($user->id == getUser('id')) {
            abort('default', 'Отсутствует переписка с самим собой!');
        }

        $totalInbox  = Inbox::query()->where('user_id', getUser('id'))->where('author_id', $user->id)->count();
        $totalOutbox = Outbox::query()->where('user_id', getUser('id'))->where('recipient_id', $user->id)->count();

        $total = $totalInbox + $totalOutbox;

        $page = paginate(setting('privatpost'), $total);

        $outbox = Outbox::query()
            ->select('id', 'user_id', 'user_id as author_id', 'text', 'created_at')
            ->where('user_id', getUser('id'))
            ->where('recipient_id', $user->id);

        $messages = Inbox::query()
            ->where('user_id', getUser('id'))
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
