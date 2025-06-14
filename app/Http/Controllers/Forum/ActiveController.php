<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActiveController extends Controller
{
    public ?User $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $login = $request->input('user', getUser('login'));
            $this->user = getUserByLogin($login);

            if (! $this->user) {
                abort(404, __('validator.user'));
            }

            return $next($request);
        });
    }

    /**
     * Вывод тем
     */
    public function topics(Request $request): View
    {
        $user = $this->user;

        $sort = $request->input('sort', 'date');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = Topic::getSorting($sort, $order);

        $topics = Topic::query()
            ->where('user_id', $user->id)
            ->orderBy(...$orderBy)
            ->with('forum', 'user', 'lastPost.user')
            ->paginate(setting('forumtem'))
            ->appends(['user' => $user->login, 'sort' => $sort, 'order' => $order]);

        return view('forums/active_topics', compact('topics', 'user', 'sorting'));
    }

    /**
     * Вывод сообщений
     */
    public function posts(Request $request): View
    {
        $user = $this->user;

        $sort = $request->input('sort', 'date');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = Post::getSorting($sort, $order);

        $posts = Post::query()
            ->where('user_id', $user->id)
            ->orderBy(...$orderBy)
            ->with('topic', 'user')
            ->paginate(setting('forumpost'))
            ->appends(['user' => $user->login, 'sort' => $sort, 'order' => $order]);

        return view('forums/active_posts', compact('posts', 'user', 'sorting'));
    }

    /**
     * Удаление сообщений
     */
    public function destroy(int $id): JsonResponse
    {
        if (! isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => __('forums.posts_deleted_moderators'),
            ]);
        }

        $post = Post::query()
            ->where('id', $id)
            ->with('topic.forum')
            ->first();

        if (! $post) {
            return response()->json([
                'success' => false,
                'message' => __('forums.post_not_exist'),
            ]);
        }

        $post->delete();
        $post->topic->decrement('count_posts');
        $post->topic->forum->decrement('count_posts');

        return response()->json([
            'success' => true,
            'message' => __('main.record_deleted_success'),
        ]);
    }
}
