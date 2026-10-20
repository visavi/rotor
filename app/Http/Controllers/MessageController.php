<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Models\Dialogue;
use App\Models\File;
use App\Models\Flood;
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
            ->with('author')
            ->orderByDesc('d.created_at')
            ->paginate(setting('privatpost'));

        return view('messages/index', compact('messages'));
    }

    /**
     * Диалог
     */
    public function talk(string $login): View
    {
        if (is_numeric($login)) {
            $user = (new User())->setAttribute('id', $login);
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
            ->pending(Message::$morphName, $this->user->id)
            ->ordered()
            ->get();

        $countMessages = $this->user->getCountNewMessages();
        $view = $user->id ? 'messages/talk' : 'messages/talk_system';

        return view($view, compact('messages', 'user', 'files', 'countMessages'));
    }

    /**
     * Отправка сообщений
     */
    public function send(MessageRequest $request, Flood $flood): RedirectResponse
    {
        $user = $request->recipient();

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $message = $user->sendMessage($this->user, $request->validated('msg'));

        File::query()
            ->pending(Message::$morphName, $this->user->id)
            ->update(['relate_id' => $message->id]);

        $flood->saveState();

        return redirect('messages/talk/' . $user->login)
            ->with('success', __('messages.success_sent'));
    }

    /**
     * Удаление переписки
     */
    public function delete(int $uid, Request $request): RedirectResponse
    {
        $page = int($request->input('page', 1));

        $dialogues = Dialogue::query()
            ->where('user_id', $this->user->id)
            ->where('author_id', $uid)
            ->get();

        // Непрочитанные удалять нельзя: иначе сообщение исчезнет, не дойдя до адресата
        $error = match (true) {
            $dialogues->isEmpty()                  => __('messages.empty_dialogue'),
            $this->user->getCountNewMessages() > 0 => __('messages.unread_messages'),
            default                                => null,
        };

        if ($error) {
            return redirect('messages?page=' . $page)
                ->withErrors($error);
        }

        foreach ($dialogues as $dialogue) {
            $dialogue->delete();
        }

        return redirect('messages?page=' . $page)
            ->with('success', __('messages.success_deleted'));
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
                ->with('author')
                ->groupBy('author_id')
                ->orderByDesc('last_created_at')
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
