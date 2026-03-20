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
    public function user(): JsonResource
    {
        $user = getUser();

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
     * Создаёт пост в теме форума
     */
    public function createPost(int $id, Request $request, Flood $flood): JsonResponse
    {
        $user = getUser();

        $topic = Topic::query()
            ->where('topics.id', $id)
            ->with('forum.parent')
            ->first();

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $lastPost = Post::query()->where('topic_id', $topic->id)->orderByDesc('id')->value('text');

        $validated = $request->validate([
            'text' => [
                'required',
                'string',
                'min:' . setting('forum_text_min'),
                'max:' . setting('forum_text_max'),
                function (string $attribute, mixed $value, Closure $fail) use ($topic, $flood, $lastPost) {
                    if ($topic->closed) {
                        $fail(__('forums.topic_closed'));
                    }

                    if ($flood->isFlood()) {
                        $fail(__('validator.flood', ['sec' => $flood->getPeriod()]));
                    }

                    if ($lastPost === $value) {
                        $fail(__('forums.post_repeat'));
                    }
                },
            ],
            'files'   => ['nullable', 'array', 'max:' . setting('maxfiles')],
            'files.*' => [
                'file',
                'max:' . setting('filesize'),
                'mimes:' . setting('file_extensions'),
            ],
        ]);

        $msg = antimat($validated['text']);

        $uploadedFiles = $request->file('files', []);

        $post = Post::query()->create([
            'topic_id'   => $topic->id,
            'user_id'    => $user->id,
            'text'       => $msg,
            'created_at' => SITETIME,
            'ip'         => getIp(),
            'brow'       => getBrowser(),
        ]);

        foreach ($uploadedFiles as $file) {
            $post->uploadFile($file);
        }

        $flood->saveState();
        sendNotify($msg, route('topics.topic', ['id' => $topic->id, 'pid' => $post->id], false), $topic->title);

        $post->load('user', 'files');

        return response()->json([
            'message' => __('main.message_added_success'),
            'post'    => PostResource::make($post),
        ], 201);
    }

    /**
     * Api диалогов
     */
    public function dialogues(Request $request): JsonResource
    {
        $user = getUser();

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
        $user = getUser();

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

        $messages = Message::query()
            ->select('d.*', 'm.id', 'm.text', 'd2.reading as recipient_read')
            ->from('messages as m')
            ->join('dialogues as d', 'd.message_id', 'm.id')
            ->leftJoin('dialogues as d2', function ($join) {
                $join->on('d.user_id', 'd2.author_id')
                    ->whereColumn('d.message_id', 'd2.message_id');
            })
            ->where('d.user_id', $user->id)
            ->where('d.author_id', $author->id)
            ->orderBy('d.created_at', $this->getOrder($request))
            ->with('user', 'author', 'files')
            ->paginate($this->getPerPage($request));

        Dialogue::query()
            ->where('user_id', $user->id)
            ->where('author_id', $author->id)
            ->where('reading', 0)
            ->update(['reading' => 1]);

        $dialogue = $messages->first();
        $dialogue?->setAttribute('all_reading', true);

        return MessageResource::collection($messages)
            ->additional(['dialogue' => DialogueResource::make($dialogue)]);
    }

    /**
     * Отправляет приватное сообщение
     */
    public function send(Request $request, Flood $flood): JsonResponse
    {
        $user = getUser();
        $login = $request->input('login');
        $recipient = getUserByLogin($login);

        $validated = $request->validate([
            'login' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($user, $recipient) {
                    if (! $recipient) {
                        $fail(__('validator.user'));
                    } elseif ($recipient->id === $user->id) {
                        $fail(__('messages.send_yourself'));
                    } elseif ($recipient->isIgnore($user)) {
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
            'files'   => ['nullable', 'array', 'max:' . setting('maxfiles')],
            'files.*' => [
                'file',
                'max:' . setting('filesize'),
                'mimes:' . setting('file_extensions'),
            ],
        ]);

        $text = antimat($validated['text']);
        $message = $recipient->sendMessage($user, $text);

        foreach ($request->file('files', []) as $file) {
            $message->uploadFile($file);
        }

        $flood->saveState();

        $message->load('user', 'files');
        $message->setAttribute('type', Message::OUT);

        return response()->json([
            'message' => __('messages.success_sent'),
            'data'    => MessageResource::make($message),
        ], 201);
    }

    /**
     * Api новых сообщений
     */
    public function newMessages(): JsonResponse
    {
        $user = getUser();

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
