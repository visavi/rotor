<?php

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Contact;
use App\Models\Flood;
use App\Models\Ignore;
use App\Models\Inbox;
use App\Models\Outbox;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;

class MessageController extends BaseController
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

        $page->totalOutbox = Outbox::query()->where('user_id', getUser('id'))->count();

        $messages = Inbox::query()
            ->where('user_id', getUser('id'))
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
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

        return view('messages/index', compact('messages', 'page', 'newprivat'));
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
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('recipient')
            ->get();

        $page->totalInbox = Inbox::query()->where('user_id', getUser('id'))->count();

        return view('messages/outbox', compact('messages', 'page'));
    }

    /**
     * Отправка сообщений
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function send(Request $request, Validator $validator): string
    {
        $login = check($request->input('user'));

        if (! empty($request->input('contact'))) {
            $login = check($request->input('contact'));
        }

        $user = User::query()->where('login', $login)->first();

        if ($request->isMethod('post')) {

            $token   = check($request->input('token'));
            $msg     = check($request->input('msg'));

            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->true($user, ['user' => 'Пользователь не найден!'])
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинное или короткое сообщение!'])
                ->equal(Flood::isFlood(), true, 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' сек!');

            if ($user) {

                $validator->notEqual($user->id, getUser('id'), ['user' => 'Нельзя отправлять письмо самому себе!']);

                if (! captchaVerify() && getUser('point') < setting('privatprotect')) {
                    $validator->addError(['protect' => 'Не удалось пройти проверку captcha!']);
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

                DB::delete('DELETE FROM `outbox` WHERE `recipient_id`=? AND `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `outbox` WHERE `recipient_id`=? ORDER BY `created_at` DESC LIMIT ' . setting('limitoutmail') . ') AS del);', [getUser('id'), getUser('id')]);

                setFlash('success', 'Ваше письмо успешно отправлено!');
                redirect('/messages');

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $contacts = Contact::query()
            ->where('user_id', getUser('id'))
            ->rightJoin('users', 'contacts.contact_id', '=', 'users.id')
            ->orderBy('users.login')
            ->get();

        return view('messages/send', compact('user', 'contacts'));
    }

    /**
     * Удаление сообщений
     *
     * @param Request   $request
     * @param Validator $validator
     */
    public function delete(Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));
        $type  = check($request->input('type'));
        $del   = intar($request->input('del'));
        $page  = int($request->input('page', 1));

        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные сообщения для удаления!');

        if ($validator->isValid()) {

            if ($type === 'outbox') {
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
        redirect('/messages' . $type . '?page=' . $page);
    }

    /**
     * Очистка сообщений
     *
     * @param Request   $request
     * @param Validator $validator
     */
    public function clear(Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));
        $type  = check($request->input('type'));
        $page  = int($request->input('page', 1));

        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->empty(getUser('newprivat'), 'У вас имеются непрочитанные сообщения!');

        if ($validator->isValid()) {

            if ($type === 'outbox') {
                Outbox::query()->where('user_id', getUser('id'))->delete();
            } else {
                Inbox::query()->where('user_id', getUser('id'))->delete();
            }

            setFlash('success', 'Ящик успешно очищен!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        $type = $type ? '/' . $type : '';
        redirect('/messages' . $type . '?page=' . $page);
    }

    /**
     * Просмотр переписки
     *
     * @param Request $request
     * @return string
     */
    public function history(Request $request): string
    {
        $login = check($request->input('user'));

        if (! $user = getUserByLogin($login)) {
            abort(404, 'Пользователя с данным логином не существует!');
        }

        if ($user->id === getUser('id')) {
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
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('author')
            ->get();

        return view('messages/history', compact('messages', 'page', 'user'));
    }
}
