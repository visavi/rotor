<?php

declare(strict_types=1);

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
            abort(403, __('main.not_authorized'));
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
            ->joinSub($latestMessage, 'latest_message', static function (JoinClause $join) {
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
     * @param int $id
     *
     * @return string
     */
    public function talk(int $id): string
    {
        $user = getUserById($id);

        if (! $user) {
            $user = new User();
            $user->id = $id;
        }

        $total = Message::query()
            ->where('user_id', $this->user->id)
            ->where('author_id', $user->id)
            ->count();

        if (! $total) {
            abort('default', 'История переписки отсутствует!');
        }

        if ($user->id === $this->user->id) {
            abort('default', 'Отсутствует переписка с самим собой!');
        }

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
     * @param Flood     $flood
     * @return void
     */
    public function send(Request $request, Validator $validator, Flood $flood): void
    {
        $login = check($request->input('user'));
        $token = check($request->input('token'));
        $msg   = check($request->input('msg'));

        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $validator->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
            ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
            ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
            ->notEqual($user->id, $this->user->id, 'Нельзя отправлять письмо самому себе!');

        if (! captchaVerify() && $this->user->point < setting('privatprotect')) {
            $validator->addError(['protect' => __('validator.captcha')]);
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

            $flood->saveState();

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

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
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
