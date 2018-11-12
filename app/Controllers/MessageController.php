<?php

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Contact;
use App\Models\Flood;
use App\Models\Ignore;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class MessageController extends BaseController
{
    public $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! $this->user = getUser()) {
            abort(403, 'Для просмотра писем необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index(): string
    {
        $total = Message::query()
            ->distinct()
            ->where('user_id', $this->user->id)
            ->count('talk_user_id');
        $page = paginate(setting('privatpost'), $total);

        $latestMessage = Message::query()
            ->select('talk_user_id', DB::raw('max(created_at) as last_created_at'))
            ->where('user_id', $this->user->id)
            ->groupBy('talk_user_id');

        $messages = Message::query()
            ->joinSub($latestMessage, 'latest_message', function (JoinClause $join) {
                $join->on('messages.created_at', 'latest_message.last_created_at')
                ->whereRaw('messages.talk_user_id = latest_message.talk_user_id');
            })
            ->where('user_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('talkUser')
            ->get();


        if ($this->user->newprivat) {
            $this->user->update([
                'newprivat'      => 0,
                'sendprivatmail' => 0,
            ]);
        }

        return view('messages/index', compact('messages', 'page'));
    }

    /**
     * Диалог
     *
     * @param string $login
     * @return string
     */
    public function talk(string $login): string
    {
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Пользователь не найден!');
        }

        if ($user->id === $this->user->id) {
            abort('default', 'Отсутствует переписка с самим собой!');
        }

        $total = Message::query()
            ->where('user_id', $this->user->id)
            ->where('talk_user_id', $user->id)
            ->count();

        $page  = paginate(setting('privatpost'), $total);

        $messages = Message::query()
            ->where('user_id', $this->user->id)
            ->where('talk_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user', 'talkUser')
            ->get();

        // Прочитано
        Message::query()
            ->where('user_id', $this->user->id)
            ->where('talk_user_id', $user->id)
            ->update(['read' => 1]);

        return view('messages/talk', compact('messages', 'user', 'page'));
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

            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));

            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->true($user, ['user' => 'Пользователь не найден!'])
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинное или короткое сообщение!'])
                ->equal(Flood::isFlood(), true, 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' сек!');

            if ($user) {
                $validator->notEqual($user->id, $this->user->id, ['user' => 'Нельзя отправлять письмо самому себе!']);

                if (! captchaVerify() && $this->user->point < setting('privatprotect')) {
                    $validator->addError(['protect' => 'Не удалось пройти проверку captcha!']);
                }

                // Проверка на игнор
                $ignoring = Ignore::query()
                    ->where('user_id', $user->id)
                    ->where('ignore_id', $this->user->id)
                    ->first();

                $validator->empty($ignoring, ['user' => 'Вы внесены в игнор-лист получателя!']);
            }

            if ($validator->isValid()) {

                $msg = antimat($msg);
                $user->increment('newprivat');

                Message::query()->create([
                    'user_id'      => $user->id,
                    'talk_user_id' => $this->user->id,
                    'text'         => $msg,
                    'type'         => 'in',
                    'created_at'   => SITETIME,
                ])->create([
                    'user_id'      => $this->user->id,
                    'talk_user_id' => $user->id,
                    'text'         => $msg,
                    'type'         => 'out',
                    'read'         => 1,
                    'created_at'   => SITETIME,
                ]);

                setFlash('success', 'Письмо успешно отправлено!');
                redirect('/messages/talk/' . $user->login);

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $contacts = Contact::query()
            ->where('user_id', $this->user->id)
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
}
