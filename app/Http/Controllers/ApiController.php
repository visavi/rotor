<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\DialogueResource;
use App\Http\Resources\FeedResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\NewMessageResource;
use App\Http\Resources\SearchResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Models\Dialogue;
use App\Models\Flood;
use App\Models\Message;
use App\Models\User;
use App\Services\CommentService;
use App\Services\ComplaintService;
use App\Services\FeedService;
use App\Services\FileService;
use App\Services\RatingService;
use App\Services\SearchService;
use App\Support\Registry;
use Closure;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
     * Авторизация и получение токена
     */
    public function auth(Request $request): JsonResponse
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = getUserByLoginOrEmail($request->input('login'));

        if (! $user || ! password_verify($request->input('password'), $user->password)) {
            abort(401, __('users.incorrect_login_or_password'));
        }

        if ($user->level === User::BANNED) {
            abort(403, 'User banned');
        }

        if (! $user->apikey) {
            $user->update(['apikey' => Str::random(32)]);
        }

        return response()->json(['token' => $user->apikey]);
    }

    /**
     * Api ленты
     */
    public function feed(): JsonResource
    {
        $feed = new FeedService();
        $posts = $feed->getItems();
        $posts->setPath(url('/api/feed'));

        // Голоса не кешируются вместе с лентой, проставляются под текущего пользователя
        $feed->applyVotes($posts->getCollection());

        return FeedResource::collection($posts);
    }

    /**
     * Api поиска по сайту
     */
    public function search(Request $request): JsonResource
    {
        $query = (string) $request->input('query', $request->input('q', ''));
        $terms = SearchService::terms($query);
        $type = SearchService::type($request->input('type'));
        $sort = SearchService::sort($request->input('sort'));

        // Слова короче трех букв fulltext не индексирует, из них запрос не собрать
        if (mb_strlen($terms) < SearchService::MIN_LENGTH || mb_strlen($terms) > SearchService::MAX_LENGTH) {
            throw ValidationException::withMessages([
                'query' => __('main.request_length'),
            ]);
        }

        $posts = SearchService::paginate($terms, $type, $sort, $this->getPerPage($request));
        $posts->setPath(url('/api/search'));
        $posts->appends($request->only(['query', 'q', 'type', 'sort', 'per_page']));

        // Список разделов для фильтра клиент берёт из /config (types.search)
        return SearchResource::collection($posts)
            ->additional([
                'query' => $query,
                'type'  => $type,
                'sort'  => $sort,
            ]);
    }

    /**
     * Api голосования за запись
     */
    public function rating(Request $request, RatingService $rating): JsonResponse
    {
        $result = $rating->vote(
            getUser(),
            $request->input('type'),
            $request->integer('id'),
            $request->input('vote'),
        );

        return response()->json($result, $result['success'] ? 200 : 422);
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
            ->additional(['dialogue' => $dialogue ? DialogueResource::make($dialogue) : null]);
    }

    /**
     * Отправляет приватное сообщение
     */
    public function createTalk(string $login, Request $request, Flood $flood): JsonResponse
    {
        $user = getUser();
        $recipient = getUserByLogin($login);

        $validated = $request->validate([
            'text' => [
                'required',
                'string',
                'min:' . setting('comment_text_min'),
                'max:' . setting('comment_text_max'),
                function (string $attribute, mixed $value, Closure $fail) use ($user, $recipient, $flood) {
                    if (! $recipient) {
                        $fail(__('validator.user'));
                    } elseif ($recipient->id === $user->id) {
                        $fail(__('messages.send_yourself'));
                    } elseif ($flood->isFlood()) {
                        $fail(__('validator.flood', ['sec' => $flood->getPeriod()]));
                    }
                },
            ],
            'files'   => ['nullable', 'array', 'max:' . setting('maxfiles')],
            'files.*' => ['file', 'max:' . FileService::maxFileSize(), 'mimes:' . setting('file_extensions')],
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
     * Удаляет переписку с пользователем
     */
    public function deleteTalk(string $login): JsonResponse
    {
        $user = getUser();
        $author = getUserByLogin($login);

        if (! $author) {
            abort(404, __('validator.user'));
        }

        // Непрочитанные удалять нельзя: иначе сообщение исчезнет, не дойдя до адресата
        if ($user->newprivat) {
            abort(422, __('messages.unread_messages'));
        }

        $deleted = Dialogue::query()
            ->where('user_id', $user->id)
            ->where('author_id', $author->id)
            ->get()
            ->each(static fn (Dialogue $dialogue) => $dialogue->delete())
            ->count();

        if (! $deleted) {
            abort(404, __('messages.empty_dialogue'));
        }

        return response()->json(['message' => __('messages.success_deleted')]);
    }

    /**
     * Жалоба на запись
     */
    public function complaint(Request $request, ComplaintService $complaint): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', ComplaintService::types())],
            'id'   => ['required', 'integer', 'min:1'],
            'page' => ['nullable'],
        ]);

        $result = $complaint->create($validated['type'], (int) $validated['id'], $request->input('page'));

        return response()->json($result, $result['success'] ? 201 : 422);
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
     * Api конфигурации
     */
    public function config(): JsonResponse
    {
        // Значения параметра type в разных ручках: набор зависит от того,
        // какие модули установлены, поэтому клиент берёт их отсюда, а не хардкодит
        $types = [
            'search'    => SearchService::types(),
            'comment'   => $this->labeled(CommentService::types()),
            'rating'    => RatingService::types(),
            'media'     => FileService::mediaTypes(),
            'file'      => FileService::fileTypes(),
            'complaint' => ComplaintService::types(),
        ];

        $data = Cache::remember('apiConfig', 600, static fn () => [
            'site' => [
                'title'             => setting('title'),
                'language'          => setting('language'),
                'money_name'        => setting('moneyname'),
                'score_name'        => setting('scorename'),
                'site_closed'       => (bool) setting('closedsite'),
                'registration_open' => (bool) setting('openreg'),
            ],
            'upload' => [
                'max_files'     => setting('maxfiles'),
                'max_file_size' => setting('filesize'),
                'extensions'    => explode(',', setting('file_extensions')),
            ],
            'message' => [
                'text_min' => setting('comment_text_min'),
                'text_max' => setting('comment_text_max'),
            ],
            // Формы регистрации и аккаунта: длины полей зашиты в ядре, цены — в настройках
            'account' => [
                'login_min'     => 3,
                'login_max'     => 20,
                'password_min'  => 6,
                'password_max'  => 20,
                'captcha_type'  => setting('captcha_type'),
                'confirm_email' => (bool) setting('regkeys'),
                'status_point'  => setting('editstatuspoint'),
                'status_money'  => setting('editstatusmoney'),
                'color_point'   => setting('editcolorpoint'),
                'color_money'   => setting('editcolormoney'),
            ],
        ]);

        // Типы и секции модулей не кешируются вместе с настройками ядра: они зависят
        // от набора включённых модулей и должны меняться сразу после включения
        $data['types'] = $types;

        // Секции модулей: какие свои настройки отдавать, модуль объявляет в module.php
        foreach (Registry::$apiConfig as $section => $settings) {
            // Строка — имя настройки, любое другое значение отдаётся как есть
            $data[$section] = array_map(
                static fn (mixed $value) => is_string($value) ? setting($value) : $value,
                $settings,
            );
        }

        return response()->json($data);
    }

    /**
     * Дополняет список типов их названиями для UI
     */
    private function labeled(array $types): array
    {
        $labels = [];

        foreach ($types as $type) {
            $labels[$type] = Registry::$labelTypes[$type] ?? $type;
        }

        return $labels;
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
