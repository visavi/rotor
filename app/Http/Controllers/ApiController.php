<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\FileResource;
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
            'success' => true,
            'data'    => [
                'login'       => $user->login,
                'email'       => $user->email,
                'name'        => $user->name,
                'level'       => $user->level,
                'country'     => $user->country,
                'city'        => $user->city,
                'language'    => $user->language,
                'info'        => $user->info,
                'site'        => $user->site,
                'phone'       => $user->phone,
                'gender'      => $user->gender,
                'birthday'    => $user->birthday,
                'visits'      => $user->visits,
                'allprivat'   => $user->getCountMessages(),
                'newprivat'   => $user->newprivat,
                'newwall'     => $user->newwall,
                'allforum'    => $user->allforum,
                'allguest'    => $user->allguest,
                'allcomments' => $user->allcomments,
                'themes'      => $user->themes,
                'timezone'    => $user->timezone,
                'point'       => $user->point,
                'money'       => $user->money,
                'status'      => $user->status ? $user->getStatus()->toHtml() : null,
                'color'       => $user->color,
                'avatar'      => $user->avatar ? asset($user->avatar) : null,
                'picture'     => $user->picture ? asset($user->picture) : null,
                'rating'      => $user->rating,
                'lastlogin'   => $user->updated_at,
            ],
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
            'success' => true,
            'data'    => [
                'login'       => $user->login,
                'name'        => $user->name,
                'level'       => $user->level,
                'country'     => $user->country,
                'city'        => $user->city,
                'info'        => $user->info,
                'site'        => $user->site,
                'gender'      => $user->gender,
                'birthday'    => $user->birthday,
                'visits'      => $user->visits,
                'allforum'    => $user->allforum,
                'allguest'    => $user->allguest,
                'allcomments' => $user->allcomments,
                'themes'      => $user->themes,
                'point'       => $user->point,
                'money'       => $user->money,
                'status'      => $user->status ? $user->getStatus()->toHtml() : null,
                'color'       => $user->color,
                'avatar'      => $user->avatar ? asset($user->avatar) : null,
                'picture'     => $user->picture ? asset($user->picture) : null,
                'rating'      => $user->rating,
                'lastlogin'   => $user->updated_at,
            ],
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
            ->paginate(setting('privatpost'));

        $messages = [];
        foreach ($dialogues as $message) {
            $message->text = bbCode($message->text);

            $messages[] = [
                'id'         => $message->id,
                'login'      => $message->author->exists ? $message->author->login : null,
                'name'       => $message->author_id ? $message->author->getName() : __('messages.system'),
                'text'       => $message->text->toHtml(),
                'type'       => $message->type,
                'created_at' => $message->created_at,
            ];
        }

        return $this->getResponse($dialogues, $messages);
    }

    /**
     * Api приватных сообщений
     */
    public function talk(string $login, Request $request): Response
    {
        $user = $request->attributes->get('user');

        if (is_numeric($login)) {
            $author = new User();
            $author->id = $login;
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
            ->paginate(setting('privatpost'));

        $msg = [];
        foreach ($messages as $message) {
            $message->text = bbCode($message->text);
            $sender = $message->type === $message::IN ? $message->author : $message->user;

            $msg[] = [
                'id'         => $message->id,
                'login'      => $sender->exists ? $sender->login : null,
                'name'       => $sender->exists ? $sender->getName() : __('messages.system'),
                'text'       => $message->text->toHtml(),
                'type'       => $message->type,
                'created_at' => $message->created_at,
                'files'      => FileResource::collection($message->files),
            ];
        }

        return $this->getResponse($messages, $msg);
    }

    /**
     * Api форума
     */
    public function forums(int $id): Response
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
            ->paginate(setting('forumtem'));

        $data = [];
        foreach ($topics as $topic) {
            $data[] = [
                'id'                   => $topic->id,
                'title'                => $topic->title,
                'login'                => $topic->user->login,
                'closed'               => $topic->closed,
                'locked'               => $topic->locked,
                'count_posts'          => $topic->count_posts,
                'visits'               => $topic->visits,
                'moderators'           => $topic->moderators,
                'note'                 => $topic->note,
                'last_post_id'         => $topic->last_post_id,
                'last_post_user_login' => $topic->lastPost->user->login,
                'close_user_id'        => $topic->close_user_id,
                'updated_at'           => $topic->updated_at,
                'created_at'           => $topic->created_at,
            ];
        }

        return $this->getResponse($topics, $data);
    }

    /**
     * Api постов темы в форуме
     */
    public function topics(int $id): Response
    {
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $posts = Post::query()
            ->where('topic_id', $id)
            ->with('user', 'files')
            ->orderBy('created_at')
            ->paginate(setting('forumpost'));

        $data = [];
        foreach ($posts as $post) {
            $post->text = bbCode($post->text);

            $data[] = [
                'id'         => $post->id,
                'login'      => $post->user->login,
                'text'       => $post->text->toHtml(),
                'rating'     => $post->rating,
                'updated_at' => $post->updated_at,
                'created_at' => $post->created_at,
                'files'      => FileResource::collection($post->files),
            ];
        }

        return $this->getResponse($posts, $data);
    }

    /**
     * Get paginate
     */
    private function getResponse(LengthAwarePaginator $collect, array $data): Response
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
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
