<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Dialogue;
use App\Models\File;
use App\Models\Flood;
use App\Models\Ignore;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class MessageController extends BaseController
{
    /**
     * @var User
     */
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
     *
     * @return string
     */
    public function index(): string
    {
        $lastMessage = Dialogue::query()
            ->select(
                'author_id',
                DB::connection()->raw('max(message_id) as message_id'),
                DB::connection()->raw('min(case when reading then 1 else 0 end) as all_reading')
            )
            ->where('user_id', $this->user->id)
            ->groupBy('author_id');

        $messages = Message::query()
            ->select('d.*', 'm.text', 'd2.all_reading', 'd3.reading as recipient_read')
            ->from('messages as m')
            ->join('dialogues as d', 'd.message_id', 'm.id')
            ->joinSub($lastMessage, 'd2', static function (JoinClause $join) {
                $join->on('d.message_id', 'd2.message_id');
            })
            ->leftJoin('dialogues as d3', function ($join) {
                $join->on('d.user_id', 'd3.author_id')
                    ->whereRaw('d.message_id = d3.message_id');
            })
            ->where('d.user_id', $this->user->id)
            ->orderByDesc('d.created_at')
            ->with('author')
            ->paginate(setting('privatpost'));

        return view('messages/index', compact('messages'));
    }

    /**
     * Диалог
     *
     * @param string $login
     *
     * @return string
     */
    public function talk(string $login): string
    {
        if (is_numeric($login)) {
            $user = new User();
            $user->id = $login;
        } else {
            $user = getUserByLogin($login);

            if (! $user) {
                abort(404, __('validator.user'));
            }
        }

        if ($user->id === $this->user->id) {
            abort('default', __('messages.empty_dialogue'));
        }

        $messages = Message::query()
            ->select('d.*', 'm.id', 'm.text', 'd2.reading as recipient_read')
            ->from('messages as m')
            ->join('dialogues as d', 'd.message_id', 'm.id')
            ->leftJoin('dialogues as d2', function ($join) {
                $join->on('d.user_id', 'd2.author_id')
                    ->whereRaw('d.message_id = d2.message_id');
            })
            ->where('d.user_id', $this->user->id)
            ->where('d.author_id', $user->id)
            ->orderByDesc('d.created_at')
            ->with('user', 'author')
            ->paginate(setting('privatpost'));

        Dialogue::query()
            ->where('user_id', $this->user->id)
            ->where('author_id', $user->id)
            ->where('reading', 0)
            ->update(['reading' => 1]);

        $files = File::query()
            ->where('relate_type', Message::$morphName)
            ->where('relate_id', 0)
            ->where('user_id', $this->user->id)
            ->get();

        $view = $user->id ? 'messages/talk' : 'messages/talk_system';

        return view($view, compact('messages', 'user', 'files'));
    }

    /**
     * Отправка сообщений
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return void
     */
    public function send(Request $request, Validator $validator, Flood $flood): void
    {
        $login = $request->input('user');
        $msg   = $request->input('msg');

        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $validator->equal($request->input('token'), $_SESSION['token'], ['msg' => __('validator.token')])
            ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
            ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
            ->notEqual($user->id, $this->user->id, __('messages.send_yourself'));

        if (! captchaVerify() && $this->user->point < setting('privatprotect')) {
            $validator->addError(['protect' => __('validator.captcha')]);
        }

        // Проверка на игнор
        $ignoring = Ignore::query()
            ->where('user_id', $user->id)
            ->where('ignore_id', $this->user->id)
            ->first();

        $validator->empty($ignoring, ['user' => __('ignores.you_are_ignoring')]);

        if ($validator->isValid()) {
            $msg = antimat($msg);

            $message =(new Message())->createDialogue($user, $this->user, $msg);

            File::query()
                ->where('relate_type', Message::$morphName)
                ->where('relate_id', 0)
                ->where('user_id', $this->user->id)
                ->update(['relate_id' => $message->id]);

            $flood->saveState();

            setFlash('success', __('messages.success_sent'));
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
        $page = int($request->input('page', 1));

        $total = Dialogue::query()
            ->where('user_id', $this->user->id)
            ->where('author_id', $uid)
            ->count();

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->notEmpty($total, ['user' => __('messages.empty_dialogue')])
            ->empty(getUser('newprivat'), __('messages.unread_messages'));

        if ($validator->isValid()) {
            Dialogue::query()
                ->where('user_id', getUser('id'))
                ->where('author_id', $uid)
                ->delete();

            setFlash('success', __('messages.success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/messages?page=' . $page);
    }

    /**
     * New messages
     *
     * @return string
     */
    public function newMessages(): string
    {
        if (! request()->ajax()) {
            redirect('/');
        }

        $dialogues = Dialogue::query()
            ->select(
                'author_id',
                DB::connection()->raw('max(created_at) as last_created_at')
            )
            ->selectRaw('count(*) as cnt')
            ->where('user_id', $this->user->id)
            ->where('reading', 0)
            ->groupBy('author_id')
            ->limit(3)
            ->get();

        if ($dialogues->isNotEmpty()) {
            $view = view('messages/_new', compact('dialogues'));

            return json_encode(['status' => 'success', 'dialogues' => $view]);
        }

        return json_encode(['status'  => 'error']);
    }
}
