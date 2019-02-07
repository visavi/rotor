<?php

namespace App\Controllers;

use App\Classes\Validator;
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
            ->count('author_id');
        $page = paginate(setting('privatpost'), $total);

        $latestMessage = Message::query()
            ->select('author_id', DB::connection()->raw('max(created_at) as last_created_at'))
            ->where('user_id', $this->user->id)
            ->groupBy('author_id');

        $messages = Message::query()
            ->joinSub($latestMessage, 'latest_message', function (JoinClause $join) {
                $join->on('messages.created_at', 'latest_message.last_created_at')
                ->whereRaw('messages.author_id = latest_message.author_id');
            })
            ->where('user_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('author')
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
    public function talk(?string $login = null): string
    {
        if ($login) {
            $user = User::query()->where('login', $login)->first();

            if (! $user) {
                abort(404, trans('validator.user'));
            }

            if ($user->id === $this->user->id) {
                abort('default', 'Отсутствует переписка с самим собой!');
            }
        } else {
            $user = new User();
            $user->id = 0;
        }

        $total = Message::query()
            ->where('user_id', $this->user->id)
            ->where('author_id', $user->id)
            ->count();

        $page  = paginate(setting('privatpost'), $total);

        $messages = Message::query()
            ->where('user_id', $this->user->id)
            ->where('author_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user', 'author')
            ->get();

        // Прочитано
        Message::query()
            ->where('user_id', $this->user->id)
            ->where('author_id', $user->id)
            ->update(['reading' => 1]);

        $view = $user->id ? 'messages/talk' : 'messages/talk_system';

        return view($view, compact('messages', 'user', 'page'));
    }

    /**
     * Отправка сообщений
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function send(Request $request, Validator $validator): void
    {
        $login = check($request->input('user'));
        $token = check($request->input('token'));
        $msg   = check($request->input('msg'));

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, trans('validator.user'));
        }

        $validator->equal($token, $_SESSION['token'], ['msg' => trans('validator.token')])
            ->length($msg, 5, setting('comment_length'), ['msg' => trans('validator.text')])
            ->equal(Flood::isFlood(), true, trans('validator.flood', ['sec' => Flood::getPeriod()]))
            ->notEqual($user->id, $this->user->id, 'Нельзя отправлять письмо самому себе!');

        if (! captchaVerify() && $this->user->point < setting('privatprotect')) {
            $validator->addError(['protect' => trans('validator.captcha')]);
        }

        // Проверка на игнор
        $ignoring = Ignore::query()
            ->where('user_id', $user->id)
            ->where('ignore_id', $this->user->id)
            ->first();

        $validator->empty($ignoring, ['user' => 'Вы внесены в игнор-лист получателя!']);

        if ($validator->isValid()) {

            $msg = antimat($msg);
            $user->increment('newprivat');

            Message::query()->create([
                'user_id'    => $user->id,
                'author_id'  => $this->user->id,
                'text'       => $msg,
                'type'       => 'in',
                'created_at' => SITETIME,
            ])->create([
                'user_id'    => $this->user->id,
                'author_id'  => $user->id,
                'text'       => $msg,
                'type'       => 'out',
                'reading'    => 1,
                'created_at' => SITETIME,
            ]);

            setFlash('success', 'Письмо успешно отправлено!');
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/messages/talk/' . $user->login);
    }

    /**
     * Удаление переписки
     *
     * @param int       $uid
     * @param Request   $request
     * @param Validator $validator
     */
    public function delete(int $uid, Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));
        $page  = int($request->input('page', 1));

        $total = Message::query()
            ->where('user_id', $this->user->id)
            ->where('author_id', $uid)
            ->count();

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
            ->notEmpty($total, ['user' => 'Переписки с данным пользователем не существует!'])
            ->empty(getUser('newprivat'), 'У вас имеются непрочитанные сообщения!');

        if ($validator->isValid()) {
            Message::query()
                ->where('user_id', getUser('id'))
                ->where('author_id', $uid)
                ->delete();

            setFlash('success', 'Сообщения успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/messages?page=' . $page);
    }
}
