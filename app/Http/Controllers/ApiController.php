<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\DialogueResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TopicResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Models\Dialogue;
use App\Models\Forum;
use App\Models\Message;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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
    public function user(Request $request): Response
    {
        $user = $request->attributes->get('user');

        return response()->json([
            'data' => new UserProfileResource($user),
        ]);
    }

    /**
     * Api пользователей
     */
    public function users(string $login): Response
    {
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Api диалогов
     */
    public function dialogues(Request $request): Response
    {
        $user = $request->attributes->get('user');

        $lastMessage = Dialogue::query()
            ->select('author_id', DB::raw('max(message_id) as message_id'))
            ->where('user_id', $user->id)
            ->groupBy('author_id');

        $dialogues = Message::query()
            ->select('d.*', 'm.text')
            ->from('messages as m')
            ->join('dialogues as d', 'd.message_id', 'm.id')
            ->joinSub($lastMessage, 'd2', static function (JoinClause $join) {
                $join->on('d.message_id', 'd2.message_id');
            })
            ->where('d.user_id', $user->id)
            ->with('author')
            ->orderByDesc('d.created_at')
            ->paginate($this->getPerPage($request, 'privatpost'));

        return $this->getResponse($dialogues, DialogueResource::collection($dialogues));
    }

    /**
     * Api приватных сообщений
     */
    public function talk(string $login, Request $request): Response
    {
        $user = $request->attributes->get('user');

        if (empty($login)) {
            $author = (new User())->setAttribute('id', 0);
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
            ->orderByDesc('d.created_at')
            ->with('user', 'author', 'files')
            ->paginate($this->getPerPage($request, 'privatpost'));

        return $this->getResponse($messages, MessageResource::collection($messages));
    }

    /**
     * Api форума
     */
    public function forums(int $id, Request $request): Response
    {
        $forum = Forum::query()->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        $topics = Topic::query()
            ->where('forum_id', $id)
            ->with('user', 'lastPost.user')
            ->orderByDesc('locked')
            ->orderByDesc('updated_at')
            ->paginate($this->getPerPage($request, 'forumtem'));

        return $this->getResponse($topics, TopicResource::collection($topics));
    }

    /**
     * Api постов темы в форуме
     */
    public function topics(int $id, Request $request): Response
    {
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $posts = Post::query()
            ->where('topic_id', $id)
            ->with('user', 'files')
            ->orderBy('created_at')
            ->paginate($this->getPerPage($request, 'forumpost'));

        return $this->getResponse($posts, PostResource::collection($posts));
    }

    /**
     * Get per page from request or setting
     */
    private function getPerPage(Request $request, string $settingKey): int
    {
        $default = (int) setting($settingKey);
        $perPage = $request->integer('per_page', $default);

        return max(1, min($perPage, 100));
    }

    /**
     * Get paginate
     */
    private function getResponse(LengthAwarePaginator $collect, mixed $data): Response
    {
        return response()->json([
            'data' => $data,
            'links'   => [
                'next' => $collect->nextPageUrl(),
                'prev' => $collect->previousPageUrl(),
            ],
            'meta' => [
                'current_page' => $collect->currentPage(),
                'last_page'    => $collect->lastPage(),
                'path'         => $collect->path(),
                'per_page'     => $collect->perPage(),
                'total'        => $collect->total(),
            ],
        ]);
    }
}
