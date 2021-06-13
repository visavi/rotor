<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dialogue;
use App\Models\Forum;
use App\Models\Message;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
    {
        return view('api/index');
    }

    /**
     * Api пользователей
     *
     * @param Request $request
     *
     * @return Response
     */
    public function user(Request $request): Response
    {
        /** @var User $user */
        $user = $request->attributes->get('user');

        return response()->json([
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
            'status'      => $user->getStatus()->toHtml(),
            'color'       => $user->color,
            'avatar'      => $user->avatar ? siteUrl(true) . $user->avatar : null,
            'picture'     => $user->picture ? siteUrl(true) . $user->picture : null,
            'rating'      => $user->rating,
            'lastlogin'   => $user->updated_at,
        ]);
    }

    /**
     * Api пользователей
     *
     * @param string  $login
     *
     * @return Response
     */
    public function users(string $login): Response
    {
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        return response()->json([
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
            'status'      => $user->getStatus()->toHtml(),
            'color'       => $user->color,
            'avatar'      => $user->avatar ? siteUrl(true) . $user->avatar : null,
            'picture'     => $user->picture ? siteUrl(true) . $user->picture : null,
            'rating'      => $user->rating,
            'lastlogin'   => $user->updated_at,
        ]);
    }

    /**
     * Api диалогов
     *
     * @param Request $request
     *
     * @return Response
     */
    public function dialogues(Request $request): Response
    {
        /** @var User $user */
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

        return response()->json([
            'currentPage'     => $dialogues->currentPage(),
            'lastPage'        => $dialogues->lastPage(),
            'nextPageUrl'     => $dialogues->nextPageUrl(),
            'previousPageUrl' => $dialogues->previousPageUrl(),
            'dialogues'       => $messages,
        ]);
    }

    /**
     * Api приватных сообщений
     *
     * @param string  $login
     * @param Request $request
     *
     * @return Response
     */
    public function talk(string $login, Request $request): Response
    {
        /** @var User $user */
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
            ->with('user', 'author')
            ->paginate(setting('privatpost'));

        $msg = [];
        foreach ($messages as $message) {
            $message->text = bbCode($message->text);
            $author = $message->type === $message::IN ? $message->author : $message->user;

            $msg[] = [
                'id'         => $message->id,
                'login'      => $author->exists ? $author->login : null,
                'name'       => $author->exists ? $author->getName() : __('messages.system'),
                'text'       => $message->text->toHtml(),
                'type'       => $message->type,
                'created_at' => $message->created_at,
            ];
        }

        return response()->json([
            'currentPage'     => $messages->currentPage(),
            'lastPage'        => $messages->lastPage(),
            'nextPageUrl'     => $messages->nextPageUrl(),
            'previousPageUrl' => $messages->previousPageUrl(),
            'messages'        => $msg,
        ]);
    }

    /**
     * Api форума
     *
     * @param int $id
     *
     * @return Response
     */
    public function forums(int $id): Response
    {
        /** @var Forum $forum */
        $forum = Forum::query()->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        $topics = Topic::query()
            ->where('forum_id', $id)
            ->orderBy('created_at')
            ->paginate(setting('forumtem'));

        $data = [];
        /** @var Topic $topic */
        foreach ($topics as $topic) {
            $data[] = [
                'id'            => $topic->id,
                'title'         => $topic->title,
                'login'         => $topic->user->login,
                'closed'        => $topic->closed,
                'locked'        => $topic->locked,
                'count_posts'   => $topic->count_posts,
                'visits'        => $topic->visits,
                'moderators'    => $topic->moderators,
                'note'          => $topic->note,
                'last_post_id'  => $topic->last_post_id,
                'close_user_id' => $topic->close_user_id,
                'updated_at'    => $topic->updated_at,
                'created_at'    => $topic->created_at,
            ];
        }

        return response()->json([
            'id'              => $forum->id,
            'sort'            => $forum->sort,
            'patent_id'       => $forum->parent_id,
            'title'           => $forum->title,
            'description'     => $forum->description,
            'count_topics'    => $forum->count_topics,
            'count_posts'     => $forum->count_posts,
            'closed'          => $forum->closed,
            'currentPage'     => $topics->currentPage(),
            'lastPage'        => $topics->lastPage(),
            'nextPageUrl'     => $topics->nextPageUrl(),
            'previousPageUrl' => $topics->previousPageUrl(),
            'topics'          => $data,
        ]);
    }

    /**
     * Api постов темы в форуме
     *
     * @param int $id
     *
     * @return Response
     */
    public function topics(int $id): Response
    {
        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $posts = Post::query()
            ->where('topic_id', $id)
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
            ];
        }

        return response()->json([
            'id'              => $topic->id,
            'forum_id'        => $topic->forum_id,
            'login'           => $topic->user->login,
            'title'           => $topic->title,
            'closed'          => $topic->closed,
            'locked'          => $topic->locked,
            'note'            => $topic->note,
            'moderators'      => $topic->moderators,
            'updated_at'      => $topic->updated_at,
            'created_at'      => $topic->created_at,
            'currentPage'     => $posts->currentPage(),
            'lastPage'        => $posts->lastPage(),
            'nextPageUrl'     => $posts->nextPageUrl(),
            'previousPageUrl' => $posts->previousPageUrl(),
            'posts'           => $data,
        ]);
    }
}
