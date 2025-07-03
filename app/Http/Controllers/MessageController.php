<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Dialogue;
use App\Models\File;
use App\Models\Flood;
use App\Models\Ignore;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    public ?User $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = $request->user();

            return $next($request);
        });
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        $lastMessage = Dialogue::query()
            ->select(
                'author_id',
                DB::raw('max(message_id) as message_id'),
                DB::raw('min(case when reading then 1 else 0 end) as all_reading')
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
                    ->whereColumn('d.message_id', 'd3.message_id');
            })
            ->where('d.user_id', $this->user->id)
            ->orderByDesc('d.created_at')
            ->with('author')
            ->paginate(setting('privatpost'));

        return view('messages/index', compact('messages'));
    }

    /**
     * Диалог
     */
    public function talk(string $login): View
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
            abort(200, __('messages.empty_dialogue'));
        }

        $messages = Message::query()
            ->select('d.*', 'm.id', 'm.text', 'd2.reading as recipient_read')
            ->from('messages as m')
            ->join('dialogues as d', 'd.message_id', 'm.id')
            ->leftJoin('dialogues as d2', function ($join) {
                $join->on('d.user_id', 'd2.author_id')
                    ->whereColumn('d.message_id', 'd2.message_id');
            })
            ->where('d.user_id', $this->user->id)
            ->where('d.author_id', $user->id)
            ->orderByDesc('d.created_at')
            ->with('user', 'author', 'files')
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
            ->orderBy('created_at')
            ->get();

        $countMessages = $this->user->newprivat;
        $view = $user->id ? 'messages/talk' : 'messages/talk_system';

        return view($view, compact('messages', 'user', 'files', 'countMessages'));
    }

    /**
     * Отправка сообщений
     */
    public function send(Request $request, Validator $validator, Flood $flood): RedirectResponse
    {
        $login = $request->input('user');
        $msg = $request->input('msg');

        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $validator->equal($request->input('_token'), csrf_token(), ['msg' => __('validator.token')])
            ->length($msg, setting('comment_text_min'), setting('comment_text_max'), ['msg' => __('validator.text')])
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
            $message = $user->sendMessage($this->user, $msg);

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

        return redirect('messages/talk/' . $user->login);
    }

    /**
     * Удаление переписки
     */
    public function delete(int $uid, Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));

        $dialogues = Dialogue::query()
            ->where('user_id', $this->user->id)
            ->where('author_id', $uid)
            ->get();

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEmpty($dialogues->count(), ['user' => __('messages.empty_dialogue')])
            ->empty(getUser('newprivat'), __('messages.unread_messages'));

        if ($validator->isValid()) {
            foreach ($dialogues as $dialogue) {
                $dialogue->delete();
            }

            setFlash('success', __('messages.success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('messages?page=' . $page);
    }

    /**
     * New messages
     */
    public function newMessages(Request $request): Response
    {
        if (! $request->ajax()) {
            return redirect('/');
        }

        $countMessages = Dialogue::query()
            ->where('user_id', $this->user->id)
            ->where('reading', 0)
            ->count();

        if ($countMessages) {
            $dialogues = Dialogue::query()
                ->select(
                    'author_id',
                    DB::raw('max(created_at) as last_created_at')
                )
                ->selectRaw('count(*) as cnt')
                ->where('user_id', $this->user->id)
                ->where('reading', 0)
                ->groupBy('author_id')
                ->limit(3)
                ->get();

            if ($dialogues->isNotEmpty()) {
                $view = view('messages/_new', compact('dialogues'))->render();

                return response()->json([
                    'success'       => true,
                    'dialogues'     => $view,
                    'countMessages' => $countMessages,
                ]);
            }
        }

        return response()->json(['success' => false]);
    }
}
