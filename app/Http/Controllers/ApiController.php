<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\DialogueResource;
use App\Http\Resources\ForumResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\NewMessageResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TopicResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Models\Dialogue;
use App\Models\Flood;
use App\Models\Forum;
use App\Models\Message;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Closure;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApiController extends Controller
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        return view('api/index');
    }

    /**
     * Api пользователей
     */
    public function user(Request $request): JsonResource
    {
        $user = $request->attributes->get('user');

        return new UserProfileResource($user);
    }

    /**
     * Api пользователей
     */
    public function users(string $login): JsonResource
    {
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        return new UserResource($user);
    }

    /**
     * Api диалогов
     */
    public function dialogues(Request $request): JsonResource
    {
        $user = $request->attributes->get('user');

        $lastMessage = Dialogue::query()
            ->select(
                'author_id',
                DB::raw('max(message_id) as message_id'),
                DB::raw('min(case when reading then 1 else 0 end) as all_reading')
            )
            ->where('user_id', $user->id)
            ->groupBy('author_id');

        $dialogues = Message::query()
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
            ->where('d.user_id', $user->id)
            ->with('author')
            ->orderBy('d.created_at', $this->getOrder($request))
            ->paginate($this->getPerPage($request));

        return DialogueResource::collection($dialogues);
    }

    /**
     * Api приватных сообщений
     */
    public function talk(string $login, Request $request): JsonResource
    {
        $user = $request->attributes->get('user');

        if (is_numeric($login)) {
            $author = (new User())->setAttribute('id', $login);
        } else {
            $author = getUserByLogin($login);

            if (! $author) {
                abort(404, __('validator.user'));
            }
        }

        if ($user->id === $author->id) {
            abort(200, __('messages.empty_dialogue'));
        }

        Dialogue::query()
            ->where('user_id', $user->id)
            ->where('author_id', $author->id)
            ->where('reading', 0)
            ->update(['reading' => 1]);

        $messages = Message::query()
            ->select('d.*', 'm.id', 'm.text')
            ->from('messages as m')
            ->join('dialogues as d', 'd.message_id', 'm.id')
            ->where('d.user_id', $user->id)
            ->where('d.author_id', $author->id)
            ->orderBy('d.created_at', $this->getOrder($request))
            ->with('user', 'author', 'files')
            ->paginate($this->getPerPage($request));

        return MessageResource::collection($messages);
    }

    /**
     * Api разделов форума
     */
    public function categoryForums(): JsonResource
    {
        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('children', 'lastTopic.lastPost.user')
            ->orderBy('sort')
            ->get();

        if ($forums->isEmpty()) {
            abort(200, __('forums.empty_forums'));
        }

        return ForumResource::collection($forums);
    }

    /**
     * Api тем форума
     */
    public function forums(int $id, Request $request): JsonResource
    {
        $forum = Forum::query()->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        $topics = Topic::query()
            ->where('forum_id', $id)
            ->with('user', 'lastPost.user')
            ->orderByDesc('locked')
            ->orderBy('updated_at', $this->getOrder($request))
            ->paginate($this->getPerPage($request));

        return TopicResource::collection($topics)
            ->additional(['forum' => ForumResource::make($forum)]);
    }

    /**
     * Api постов темы в форуме
     */
    public function topics(int $id, Request $request): JsonResource
    {
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $posts = Post::query()
            ->where('topic_id', $id)
            ->with('user', 'files')
            ->orderBy('created_at', $this->getOrder($request, 'asc'))
            ->paginate($this->getPerPage($request));

        return PostResource::collection($posts)
            ->additional(['topic' => TopicResource::make($topic)]);
    }

    /**
     * Отправляет приватное сообщение
     */
    public function send(Request $request, Flood $flood): JsonResponse
    {
        $user = $request->attributes->get('user');
        $login = $request->input('login');
        $recipient = getUserByLogin($login);

        $validated = $request->validate([
            'login' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($user, $recipient) {
                    if (! $recipient) {
                        return $fail(__('validator.user'));
                    }

                    if ($recipient->id === $user->id) {
                        return $fail(__('messages.send_yourself'));
                    }

                    if ($recipient->isIgnore($user)) {
                        $fail(__('ignores.you_are_ignoring'));
                    }
                },
            ],
            'text' => [
                'required',
                'string',
                'min:' . setting('comment_text_min'),
                'max:' . setting('comment_text_max'),
                function (string $attribute, mixed $value, Closure $fail) use ($flood) {
                    if ($flood->isFlood()) {
                        $fail(__('validator.flood', ['sec' => $flood->getPeriod()]));
                    }
                },
            ],
        ]);

        $text = antimat($validated['text']);
        $recipient->sendMessage($user, $text);

        $flood->saveState();

        return response()->json(['message' => __('messages.success_sent')]);
    }

    /**
     * Api новых сообщений
     */
    public function newMessages(Request $request): JsonResponse
    {
        $user = $request->attributes->get('user');

        $countMessages = Dialogue::query()
            ->where('user_id', $user->id)
            ->where('reading', 0)
            ->count();

        if (! $countMessages) {
            return response()->json(['count' => 0, 'dialogues' => []]);
        }

        $dialogues = Dialogue::query()
            ->select(
                'author_id',
                DB::raw('max(created_at) as last_created_at')
            )
            ->selectRaw('count(*) as cnt')
            ->where('user_id', $user->id)
            ->where('reading', 0)
            ->groupBy('author_id')
            ->with('author')
            ->get();

        return response()->json([
            'count'     => $countMessages,
            'dialogues' => NewMessageResource::collection($dialogues),
        ]);
    }

    /**
     * Get order direction from request
     */
    private function getOrder(Request $request, string $default = 'desc'): string
    {
        $order = $request->input('order', $default);

        return in_array($order, ['asc', 'desc']) ? $order : $default;
    }

    /**
     * Get per page from request
     */
    private function getPerPage(Request $request): int
    {
        $perPage = $request->integer('per_page', 10);

        return max(1, min($perPage, 100));
    }
}
